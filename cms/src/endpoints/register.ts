import type { PayloadHandler } from 'payload';
import nodemailer from 'nodemailer';

export const registerEndpoint: PayloadHandler = async (req) => {
  const { payload } = req;
  const data = await req.json!();

  const { firstName, lastName, email, phone, eventId } = data;

  if (!firstName || !lastName || !email || !eventId) {
    return Response.json({ error: 'Bitte fülle alle Pflichtfelder aus.' }, { status: 400 });
  }

  // Find or create participant
  const existingParticipants = await payload.find({
    collection: 'participants',
    where: { email: { equals: email } },
    limit: 1,
  });

  let participant;
  if (existingParticipants.docs.length > 0) {
    participant = existingParticipants.docs[0];
    // Update name/phone if changed
    await payload.update({
      collection: 'participants',
      id: participant.id,
      data: { firstName, lastName, phone: phone || participant.phone },
    });
  } else {
    participant = await payload.create({
      collection: 'participants',
      data: { firstName, lastName, email, phone },
    });
  }

  // Check if already registered
  const existingReg = await payload.find({
    collection: 'registrations',
    where: {
      and: [
        { event: { equals: eventId } },
        { participant: { equals: participant.id } },
        { status: { not_equals: 'cancelled' } },
      ],
    },
    limit: 1,
  });

  if (existingReg.docs.length > 0) {
    return Response.json({ error: 'Du bist bereits für diese Veranstaltung angemeldet.' }, { status: 409 });
  }

  // Check capacity
  const event = await payload.findByID({ collection: 'events', id: eventId });
  const registrations = await payload.find({
    collection: 'registrations',
    where: {
      and: [{ event: { equals: eventId } }, { status: { in: ['registered', 'attended'] } }],
    },
    limit: 0,
  });

  const spotsUsed = registrations.totalDocs;
  const isFull = spotsUsed >= (event.maxParticipants as number);

  const registration = await payload.create({
    collection: 'registrations',
    data: {
      event: eventId,
      participant: participant.id,
      status: isFull ? 'waitlist' : 'registered',
    },
  });

  // Send confirmation email
  try {
    const transporter = nodemailer.createTransport({
      host: process.env.SMTP_HOST,
      port: Number(process.env.SMTP_PORT || 587),
      auth: {
        user: process.env.SMTP_USER,
        pass: process.env.SMTP_PASS,
      },
    });

    await transporter.sendMail({
      from: process.env.MAIL_FROM || 'hallo@mens-circle.de',
      to: email,
      subject: `Anmeldung bestätigt: ${event.title}`,
      html: `
        <h2>Hallo ${firstName},</h2>
        <p>deine Anmeldung für <strong>${event.title}</strong> wurde ${isFull ? 'auf die Warteliste gesetzt' : 'bestätigt'}.</p>
        <p><strong>Datum:</strong> ${new Date(event.eventDate as string).toLocaleDateString('de-DE')}</p>
        <p><strong>Uhrzeit:</strong> ${event.startTime} – ${event.endTime} Uhr</p>
        <p><strong>Ort:</strong> ${event.location}</p>
        <p>Wir freuen uns auf dich!</p>
        <p>Dein Männerkreis-Team</p>
      `,
    });
  } catch (e) {
    console.error('Email send error:', e);
  }

  return Response.json({
    success: true,
    message: isFull
      ? 'Du wurdest auf die Warteliste gesetzt. Wir melden uns, sobald ein Platz frei wird.'
      : 'Deine Anmeldung war erfolgreich! Du erhältst eine Bestätigung per E-Mail.',
    status: registration.status,
  });
};
