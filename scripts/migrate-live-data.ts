#!/usr/bin/env bun

/**
 * Migration script to import live data from JSON files into TYPO3 v14.1 database
 *
 * Usage (inside DDEV): ddev exec bun run scripts/migrate-live-data.ts
 */

import path from "node:path";

const LIVE_DATA_DIR = "var/transient/live";
const PID = 1; // Page ID where records should be stored

// Database configuration (DDEV defaults)
const DB_CONFIG = {
  host: process.env.DB_HOST || "db",
  port: parseInt(process.env.DB_PORT || "3306"),
  database: process.env.DB_NAME || "db",
  user: process.env.DB_USER || "db",
  password: process.env.DB_PASSWORD || "db",
};

interface LiveEvent {
  id: number;
  title: string;
  slug: string;
  description: string;
  event_date: string;
  start_time: string;
  end_time: string;
  location: string;
  location_details: string;
  max_participants: number;
  cost_basis: string;
  is_published: boolean;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
  image: string | null;
  street: string | null;
  postal_code: string | null;
  city: string | null;
}

interface LiveParticipant {
  id: number;
  first_name: string;
  last_name: string;
  email: string;
  phone: string | null;
  created_at: string;
  updated_at: string;
}

interface LiveRegistration {
  id: number;
  event_id: number;
  participant_id: number;
  status: string;
  registered_at: string;
  cancelled_at: string | null;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
}

interface LiveNewsletter {
  id: number;
  subject: string;
  content: string;
  sent_at: string | null;
  recipient_count: number;
  status: string;
  created_at: string;
  updated_at: string;
}

interface LiveNewsletterSubscription {
  id: number;
  participant_id: number;
  token: string;
  subscribed_at: string;
  confirmed_at: string | null;
  unsubscribed_at: string | null;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
}

interface LiveTestimonial {
  id: number;
  quote: string;
  author_name: string;
  role: string | null;
  is_published: boolean;
  published_at: string | null;
  sort_order: number;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
}

interface LiveContentBlock {
  id: number;
  type: string;
  data: string;
  block_id: string;
  order: number;
  created_at: string;
  updated_at: string;
  page_id: number;
}

async function loadJSON<T>(filename: string): Promise<T[]> {
  const filePath = path.join(LIVE_DATA_DIR, filename);
  const file = Bun.file(filePath);
  return await file.json();
}

function parseTimestamp(timestamp: string | null): number {
  if (!timestamp) return 0;
  return Math.floor(new Date(timestamp).getTime() / 1000);
}

function parseDatetime(datetime: string): number {
  return Math.floor(new Date(datetime).getTime() / 1000);
}

function escapeSQL(value: any, allowNull: boolean = true): string {
  if (value === null || value === undefined) {
    return allowNull ? "NULL" : "''";
  }
  if (typeof value === "number") {
    return value.toString();
  }
  if (typeof value === "boolean") {
    return value ? "1" : "0";
  }
  // Escape single quotes for SQL
  return `'${value.toString().replace(/'/g, "''")}'`;
}

async function generateMigrationSQL() {
  const sqlStatements: string[] = [];

  // Header
  sqlStatements.push("-- TYPO3 v14.1 Data Migration");
  sqlStatements.push("-- Generated: " + new Date().toISOString());
  sqlStatements.push("-- Source: Live database JSON export\n");

  // Clean existing data (optional - comment out if you want to keep existing data)
  sqlStatements.push("-- Clean existing data (optional)");
  sqlStatements.push("-- DELETE FROM tx_menscircle_domain_model_participant;");
  sqlStatements.push("-- DELETE FROM tx_menscircle_domain_model_event;");
  sqlStatements.push("-- DELETE FROM tx_menscircle_domain_model_registration;");
  sqlStatements.push("-- DELETE FROM tx_menscircle_domain_model_newsletter;");
  sqlStatements.push("-- DELETE FROM tx_menscircle_domain_model_newslettersubscription;");
  sqlStatements.push("-- DELETE FROM tx_menscircle_domain_model_testimonial;\n");

  // Migrate Participants
  console.log("Generating SQL for participants...");
  const participants = await loadJSON<LiveParticipant>("participants.json");
  sqlStatements.push("-- Participants");
  for (const p of participants) {
    const createdAt = parseTimestamp(p.created_at);
    const tstamp = parseTimestamp(p.updated_at);
    const firstName = p.first_name || "";
    const lastName = p.last_name || "";

    sqlStatements.push(
      `INSERT INTO tx_menscircle_domain_model_participant (pid, uid, first_name, last_name, email, phone, created_at, crdate, tstamp, deleted, hidden) VALUES (${PID}, ${p.id}, ${escapeSQL(firstName)}, ${escapeSQL(lastName)}, ${escapeSQL(p.email)}, ${escapeSQL(p.phone, false)}, ${createdAt}, ${createdAt}, ${tstamp}, 0, 0);`
    );
  }
  console.log(`  ✓ ${participants.length} participants`);

  // Migrate Events
  console.log("Generating SQL for events...");
  const events = await loadJSON<LiveEvent>("events.json");
  sqlStatements.push("\n-- Events");
  for (const e of events) {
    const eventDate = parseDatetime(e.event_date);
    const eventEndDate = e.end_time
      ? parseDatetime(`${e.event_date.split(" ")[0]} ${e.end_time}`)
      : 0;
    const deleted = e.deleted_at ? 1 : 0;
    const hidden = e.is_published ? 0 : 1;
    const crdate = parseTimestamp(e.created_at);
    const tstamp = parseTimestamp(e.updated_at);

    sqlStatements.push(
      `INSERT INTO tx_menscircle_domain_model_event (pid, uid, title, slug, description, location, location_details, street, postal_code, city, cost_basis, event_date, event_end_date, max_participants, is_published, crdate, tstamp, deleted, hidden) VALUES (${PID}, ${e.id}, ${escapeSQL(e.title)}, ${escapeSQL(e.slug)}, ${escapeSQL(e.description)}, ${escapeSQL(e.location)}, ${escapeSQL(e.location_details, false)}, ${escapeSQL(e.street, false)}, ${escapeSQL(e.postal_code, false)}, ${escapeSQL(e.city, false)}, ${escapeSQL(e.cost_basis, false)}, ${eventDate}, ${eventEndDate}, ${e.max_participants}, ${e.is_published ? 1 : 0}, ${crdate}, ${tstamp}, ${deleted}, ${hidden});`
    );
  }
  console.log(`  ✓ ${events.length} events`);

  // Migrate Registrations
  console.log("Generating SQL for registrations...");
  const registrations = await loadJSON<LiveRegistration>("registrations.json");
  sqlStatements.push("\n-- Registrations");
  for (const r of registrations) {
    const isConfirmed = r.status === "registered" ? 1 : 0;
    const confirmedAt = r.registered_at ? parseTimestamp(r.registered_at) : 0;
    const cancelledAt = r.cancelled_at ? parseTimestamp(r.cancelled_at) : 0;
    const createdAt = parseTimestamp(r.created_at);
    const tstamp = parseTimestamp(r.updated_at);
    const deleted = r.deleted_at ? 1 : 0;

    sqlStatements.push(
      `INSERT INTO tx_menscircle_domain_model_registration (pid, uid, event, participant, status, is_confirmed, confirmed_at, cancelled_at, created_at, crdate, tstamp, deleted, hidden) VALUES (${PID}, ${r.id}, ${r.event_id}, ${r.participant_id}, ${escapeSQL(r.status)}, ${isConfirmed}, ${confirmedAt}, ${cancelledAt}, ${createdAt}, ${createdAt}, ${tstamp}, ${deleted}, 0);`
    );
  }
  console.log(`  ✓ ${registrations.length} registrations`);

  // Migrate Newsletters
  console.log("Generating SQL for newsletters...");
  const newsletters = await loadJSON<LiveNewsletter>("newsletters.json");
  sqlStatements.push("\n-- Newsletters");
  for (const n of newsletters) {
    const isSent = n.status === "sent" ? 1 : 0;
    const sentAt = n.sent_at ? parseTimestamp(n.sent_at) : 0;
    const crdate = parseTimestamp(n.created_at);
    const tstamp = parseTimestamp(n.updated_at);

    sqlStatements.push(
      `INSERT INTO tx_menscircle_domain_model_newsletter (pid, uid, title, subject, content, is_sent, sent_at, recipient_count, crdate, tstamp, deleted, hidden) VALUES (${PID}, ${n.id}, ${escapeSQL(n.subject)}, ${escapeSQL(n.subject)}, ${escapeSQL(n.content)}, ${isSent}, ${sentAt}, ${n.recipient_count}, ${crdate}, ${tstamp}, 0, 0);`
    );
  }
  console.log(`  ✓ ${newsletters.length} newsletters`);

  // Migrate Newsletter Subscriptions
  console.log("Generating SQL for newsletter subscriptions...");
  const subscriptions = await loadJSON<LiveNewsletterSubscription>("newsletter_subscriptions.json");
  const participantMap = new Map(participants.map((p) => [p.id, p]));

  sqlStatements.push("\n-- Newsletter Subscriptions");
  for (const s of subscriptions) {
    const participant = participantMap.get(s.participant_id);
    if (!participant) {
      console.log(`  ! Skipping subscription ${s.id}: participant ${s.participant_id} not found`);
      continue;
    }

    const isConfirmed = s.confirmed_at ? 1 : 0;
    const confirmedAt = s.confirmed_at ? parseTimestamp(s.confirmed_at) : 0;
    const createdAt = parseTimestamp(s.created_at);
    const tstamp = parseTimestamp(s.updated_at);
    const deleted = s.deleted_at ? 1 : 0;
    const hidden = s.unsubscribed_at ? 1 : 0;

    sqlStatements.push(
      `INSERT INTO tx_menscircle_domain_model_newslettersubscription (pid, uid, participant, email, first_name, is_confirmed, confirmation_token, unsubscribe_token, confirmed_at, created_at, crdate, tstamp, deleted, hidden) VALUES (${PID}, ${s.id}, ${s.participant_id}, ${escapeSQL(participant.email)}, ${escapeSQL(participant.first_name, false)}, ${isConfirmed}, ${escapeSQL(s.token)}, ${escapeSQL(s.token)}, ${confirmedAt}, ${createdAt}, ${createdAt}, ${tstamp}, ${deleted}, ${hidden});`
    );
  }
  console.log(`  ✓ ${subscriptions.length} newsletter subscriptions`);

  // Migrate Testimonials
  console.log("Generating SQL for testimonials...");
  const testimonials = await loadJSON<LiveTestimonial>("testimonials.json");
  const activeTestimonials = testimonials.filter((t) => !t.deleted_at);

  sqlStatements.push("\n-- Testimonials");
  for (const t of activeTestimonials) {
    const isApproved = t.is_published ? 1 : 0;
    const crdate = parseTimestamp(t.created_at);
    const tstamp = parseTimestamp(t.updated_at);

    sqlStatements.push(
      `INSERT INTO tx_menscircle_domain_model_testimonial (pid, uid, author_name, role, content, is_approved, crdate, tstamp, deleted, hidden) VALUES (${PID}, ${t.id}, ${escapeSQL(t.author_name)}, ${escapeSQL(t.role)}, ${escapeSQL(t.quote)}, ${isApproved}, ${crdate}, ${tstamp}, 0, 0);`
    );
  }
  console.log(`  ✓ ${activeTestimonials.length} testimonials`);

  // Migrate Content Blocks
  console.log("Generating SQL for content blocks...");
  const blocks = await loadJSON<LiveContentBlock>("content_blocks.json");

  const typeMapping: Record<string, string> = {
    hero: "menscircle_hero",
    intro: "menscircle_intro",
    testimonials: "menscircle_testimonials_section",
    moderator: "menscircle_moderator",
    journey_steps: "menscircle_journey",
    faq: "menscircle_faq",
    newsletter: "menscircle_newsletter_section",
    cta: "menscircle_cta",
    whatsapp_community: "menscircle_whatsapp",
    text_section: "menscircle_textsection",
  };

  sqlStatements.push("\n-- Content Blocks (tt_content)");

  for (const block of blocks) {
    const cType = typeMapping[block.type];
    if (!cType) {
      console.log(`  ! Skipping unknown block type: ${block.type}`);
      continue;
    }

    const data = JSON.parse(block.data);
    const crdate = parseTimestamp(block.created_at);
    const tstamp = parseTimestamp(block.updated_at);

    let header = "";
    let subheader = "";
    let bodytext = "";
    let piFlexform = "";

    switch (block.type) {
      case "hero":
        header = data.label || "";
        subheader = data.title || "";
        bodytext = data.description || "";
        piFlexform = `<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
<T3FlexForms>
  <data>
    <sheet index="sDEF">
      <language index="lDEF">
        <field index="button_text">
          <value index="vDEF">${data.button_text || ""}</value>
        </field>
        <field index="button_link">
          <value index="vDEF">${data.button_link || ""}</value>
        </field>
      </language>
    </sheet>
  </data>
</T3FlexForms>`;
        break;

      case "text_section":
        header = data.eyebrow || "";
        subheader = data.title || "";
        bodytext = data.content || "";
        break;

      case "intro":
        header = data.eyebrow || "";
        subheader = data.title || "";
        bodytext = data.text || "";
        piFlexform = `<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
<T3FlexForms>
  <data>
    <sheet index="sDEF">
      <language index="lDEF">
        <field index="quote">
          <value index="vDEF">${data.quote || ""}</value>
        </field>
        <field index="values">
          <value index="vDEF">${escapeSQL(JSON.stringify(data.values || []))}</value>
        </field>
      </language>
    </sheet>
  </data>
</T3FlexForms>`;
        break;

      case "cta":
        header = data.eyebrow || "";
        subheader = data.title || "";
        bodytext = data.text || "";
        piFlexform = `<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
<T3FlexForms>
  <data>
    <sheet index="sDEF">
      <language index="lDEF">
        <field index="button_text">
          <value index="vDEF">${data.button_text || ""}</value>
        </field>
        <field index="button_link">
          <value index="vDEF">${data.button_link || ""}</value>
        </field>
      </language>
    </sheet>
  </data>
</T3FlexForms>`;
        break;

      case "faq":
        header = data.eyebrow || "";
        subheader = data.title || "";
        bodytext = data.intro || "";
        piFlexform = `<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
<T3FlexForms>
  <data>
    <sheet index="sDEF">
      <language index="lDEF">
        <field index="items">
          <value index="vDEF">${escapeSQL(JSON.stringify(data.items || []))}</value>
        </field>
      </language>
    </sheet>
  </data>
</T3FlexForms>`;
        break;

      case "journey_steps":
        header = data.eyebrow || "";
        subheader = data.title || "";
        bodytext = data.subtitle || "";
        piFlexform = `<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
<T3FlexForms>
  <data>
    <sheet index="sDEF">
      <language index="lDEF">
        <field index="steps">
          <value index="vDEF">${escapeSQL(JSON.stringify(data.steps || []))}</value>
        </field>
      </language>
    </sheet>
  </data>
</T3FlexForms>`;
        break;

      case "moderator":
        header = data.eyebrow || "";
        subheader = data.name || "";
        bodytext = data.bio || "";
        piFlexform = `<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
<T3FlexForms>
  <data>
    <sheet index="sDEF">
      <language index="lDEF">
        <field index="quote">
          <value index="vDEF">${data.quote || ""}</value>
        </field>
      </language>
    </sheet>
  </data>
</T3FlexForms>`;
        break;

      case "newsletter":
        header = data.eyebrow || "";
        subheader = data.title || "";
        bodytext = data.text || "";
        break;

      case "testimonials":
        header = data.eyebrow || "";
        subheader = data.title || "";
        bodytext = "";
        break;

      case "whatsapp_community":
        header = "";
        subheader = "";
        bodytext = "";
        break;
    }

    sqlStatements.push(
      `INSERT INTO tt_content (pid, uid, CType, header, subheader, bodytext, pi_flexform, sorting, crdate, tstamp, deleted, hidden) VALUES (${block.page_id}, ${block.id}, ${escapeSQL(cType)}, ${escapeSQL(header)}, ${escapeSQL(subheader)}, ${escapeSQL(bodytext)}, ${escapeSQL(piFlexform)}, ${(block.order + 1) * 256}, ${crdate}, ${tstamp}, 0, 0);`
    );
  }
  console.log(`  ✓ ${blocks.length} content blocks`);

  return sqlStatements.join("\n");
}

async function main() {
  console.log("=".repeat(80));
  console.log("TYPO3 v14.1 Data Migration SQL Generator");
  console.log("=".repeat(80));
  console.log("\nGenerating SQL statements from JSON files...\n");

  try {
    const sql = await generateMigrationSQL();

    // Write SQL to file
    const outputFile = "var/transient/migration.sql";
    await Bun.write(outputFile, sql);

    console.log("\n" + "=".repeat(80));
    console.log("✓ SQL file generated successfully!");
    console.log("=".repeat(80));
    console.log(`\nOutput file: ${outputFile}`);
    console.log("\nTo import into TYPO3 database, run:");
    console.log(`  ddev mysql < ${outputFile}`);
    console.log("=".repeat(80));
  } catch (error) {
    console.error("\n" + "=".repeat(80));
    console.error("✗ Migration failed:", error);
    console.error("=".repeat(80));
    process.exit(1);
  }
}

main();
