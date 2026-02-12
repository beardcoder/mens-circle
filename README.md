# Männerkreis Niederbayern / Straubing

Community platform for organizing men's circle events, managing registrations, and newsletters.

## Tech Stack

| Component    | Technology                         |
| ------------ | ---------------------------------- |
| **CMS**      | Payload CMS 3 (Next.js)            |
| **Frontend** | Astro.js 5 (SSR with Node adapter) |
| **Database** | SQLite                             |
| **Styling**  | Custom CSS (OKLCH color system)    |

## Architecture

```
├── cms/           # Payload CMS – Admin Panel + REST API (port 3001)
├── web/           # Astro.js – Frontend Website (port 4321)
└── media/         # Uploaded media files
```

## Getting Started

```bash
# Install dependencies
bun install

# Copy environment files
cp cms/.env.example cms/.env
cp web/.env.example web/.env

# Start both apps
bun dev
```

- **CMS Admin:** http://localhost:3001/admin
- **Frontend:** http://localhost:4321

On first launch, create an admin user at `/admin`.

## Data Model

```
Events ──hasMany──> Registrations ──belongsTo──> Participants
                                                     │
Newsletters                                         hasOne
NewsletterSubscriptions ──belongsTo─────────────────┘
Testimonials (standalone)
Pages ──has──> Content Blocks
SiteSettings (global)
```

## Content Blocks

Pages use a dynamic block system. Available blocks:

- `hero` – Full-screen hero section
- `intro` – Two-column intro layout
- `textSection` – Rich text content
- `valueItems` – Value cards grid
- `moderator` – Profile/bio section
- `journeySteps` – Step-by-step process
- `testimonials` – Community quotes (auto-loaded)
- `faq` – Accordion FAQ
- `newsletter` – Email signup form
- `cta` – Call-to-action section
- `whatsappCommunity` – WhatsApp join section

## API Endpoints

| Method | Endpoint                  | Purpose                 |
| ------ | ------------------------- | ----------------------- |
| POST   | `/api/register`           | Event registration      |
| POST   | `/api/subscribe`          | Newsletter subscription |
| GET    | `/api/unsubscribe/:token` | Newsletter unsubscribe  |
| POST   | `/api/send-newsletter`    | Send newsletter (admin) |

## Routes (Frontend)

| Route                             | Purpose                            |
| --------------------------------- | ---------------------------------- |
| `/`                               | Homepage (dynamic blocks from CMS) |
| `/event`                          | Redirect to next event             |
| `/event/[slug]`                   | Event detail + registration        |
| `/newsletter/unsubscribe/[token]` | Unsubscribe                        |
| `/[slug]`                         | Dynamic CMS pages                  |

## Development

```bash
bun dev:cms    # Only CMS
bun dev:web    # Only Frontend
bun run build  # Production build
```
