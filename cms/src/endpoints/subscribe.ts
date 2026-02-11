import type { PayloadHandler } from 'payload'
import nodemailer from 'nodemailer'

export const subscribeEndpoint: PayloadHandler = async (req) => {
  const { payload } = req
  const data = await req.json!()

  const { email, firstName, lastName } = data

  if (!email) {
    return Response.json(
      { error: 'Bitte gib deine E-Mail-Adresse an.' },
      { status: 400 },
    )
  }

  // Find or create participant
  const existingParticipants = await payload.find({
    collection: 'participants',
    where: { email: { equals: email } },
    limit: 1,
  })

  let participant
  if (existingParticipants.docs.length > 0) {
    participant = existingParticipants.docs[0]
  } else {
    participant = await payload.create({
      collection: 'participants',
      data: {
        firstName: firstName || 'Newsletter',
        lastName: lastName || 'Abonnent',
        email,
      },
    })
  }

  // Check if already subscribed
  const existingSub = await payload.find({
    collection: 'newsletter-subscriptions',
    where: {
      and: [
        { participant: { equals: participant.id } },
        { status: { equals: 'active' } },
      ],
    },
    limit: 1,
  })

  if (existingSub.docs.length > 0) {
    return Response.json(
      { error: 'Du bist bereits für den Newsletter angemeldet.' },
      { status: 409 },
    )
  }

  const subscription = await payload.create({
    collection: 'newsletter-subscriptions',
    data: {
      participant: participant.id,
      status: 'active',
    },
  })

  // Send welcome email
  try {
    const transporter = nodemailer.createTransport({
      host: process.env.SMTP_HOST,
      port: Number(process.env.SMTP_PORT || 587),
      auth: {
        user: process.env.SMTP_USER,
        pass: process.env.SMTP_PASS,
      },
    })

    const siteUrl = process.env.SITE_URL || 'http://localhost:4321'

    await transporter.sendMail({
      from: process.env.MAIL_FROM || 'hallo@mens-circle.de',
      to: email,
      subject: 'Willkommen beim Männerkreis Newsletter',
      html: `
        <h2>Willkommen!</h2>
        <p>Du hast dich erfolgreich für den Newsletter des Männerkreis Niederbayern/ Straubing angemeldet.</p>
        <p>Wir informieren dich über kommende Treffen und Neuigkeiten.</p>
        <p>Falls du den Newsletter abbestellen möchtest, klicke <a href="${siteUrl}/newsletter/unsubscribe/${subscription.token}">hier</a>.</p>
        <p>Dein Männerkreis-Team</p>
      `,
    })
  } catch (e) {
    console.error('Email send error:', e)
  }

  return Response.json({
    success: true,
    message: 'Du hast dich erfolgreich für den Newsletter angemeldet!',
  })
}
