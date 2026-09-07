# Votaz — Electoral Commission Platform

A Symfony 8.1 / PHP 8.4 application that runs a national electoral commission's
public website: registration, elections, parties, polls, forums, round tables,
and a voting-day **party agent / observer** workflow with photo evidence.

## Prerequisites

- **PHP >= 8.4** with `curl`, `ctype` and `mbstring` extensions (check with `php -v`)
- **Composer** (https://getcomposer.org)
- **SQLite** (bundled with PHP; optionally the `sqlite3` CLI for `.db` inspection)
- **Symfony CLI** (optional but recommended) — https://symfony.com/download
- No database server or Redis is required locally: SQLite + file locks + a
  `null://` mailer are the dev defaults.

## Installation

```bash
composer install
cp .env .env.local                # optional local overrides (secrets go here)
php bin/console doctrine:migrations:migrate
```

Dev defaults are already set in `.env` (`DATABASE_URL` → `var/app.db`, a `flock://`
lock DSN, and `MAILER_DSN=null://null`). For anything environment-specific, put it
in `.env.local` — never edit `.env` for secrets.

### Demo data (optional, but very useful)

```bash
php bin/console app:seed-demo
```

Seeds five districts, four parties, three elections (one with published results),
a poll, forum/round-table content, ten voters, **four party agents**
pre-assigned to a polling station, and one sample observation report with a
photo and party tallies.

To create an administrator account from scratch instead of using the seeded one
(pass the values up front to avoid the interactive prompt):

```bash
php bin/console app:create-admin admin@votaz.example 'S3curePass!'
```

## Running the app

```bash
symfony serve -d        # http://127.0.0.1:8000  (needs the Symfony CLI)
```

to stop it:

```bash
symfony server:stop
```

or without the CLI:

```bash
php -S 127.0.0.1:8000 -t public
```

- Web profiler: `/_profiler`
- Logs: `var/log/dev.log`
- SQLite database: `var/app.db`

### Demo credentials

| Role | Email / username | Password |
|------|------------------|----------|
| Commission admin | `admin@votaz.example` | `AdminPass123!` |
| Party agent (seeded) | `zainab.ibrahim@example.org` | `AgentPass123!` |
| Voters | `grace.osei@example.gov` (and 9 more) | `VoterPass123!` |

The seeded voters are `CONFIRMED` and registered for an election, so the
registration → approval → receipt flow can be exercised. Any seeded password is
only used in the demo data — change them if the app ever serves real traffic.

## Project flow

The platform serves four journeys.

### 1. Voter registration & confirmation

1. A citizen creates an account at `/register` using their national ID and a real
   email address. A pending `Voter` is created with `voter_number`,
   `confirmation_code`, username = email, and a random throwaway password.
2. An OTP confirmation code is generated and "sent". With the `null` mailer the
   code is surfaced on the confirmation page (`otp_dev_code` session value) for
   development; in production a real transport sends it by email (`OtpMailer`).
3. The voter confirms at `/register/confirm`, setting their own password. The
   records become `CONFIRMED` once the commission admin sets the status at
   `/admin/voters`.

### 2. Election registration

1. Voters browse `/elections` and register for an election (general, byelection,
   partial, referendum). Their `ElectionRegistration` starts `PENDING`.
2. The commission approves or rejects each registration
   (`/admin/elections/{id}/voters`). Approval assigns a receipt number, e.g.
   `REG-2026-123456`.
3. Voters see their receipt in `/profile` and can download it at
   `/profile/receipt/{id}`.

### 3. Party agent / observer on voting day

1. The commission registers **party agents** — trusted party representatives —
   at `/admin/agents` (email, party, agent code, password).
2. The commission assigns an agent to a poll (election + district + polling
   station) at `/admin/agents/{id}/assign`; the assignment starts `ACTIVE`.
3. The agent signs in at `/agent/login` and reaches `/agent`, where their active
   assignments are listed.
4. On voting day the agent submits an **observation report**
   (`/agent/assignment/{id}/report/new`):
   - observation booleans (orderly opening, orderly close, irregularities) plus a
     free-text irregularity detail,
   - photos as evidence (up to 5, `public/uploads/observations/`),
   - observed per-party tallies that were witnessed being counted
     (pre-filled from the election's approved candidates; leave 0 to skip).
   Submitting marks the assignment `COMPLETED` (one report per assignment).
5. The commission reviews flagged reports for anomalies at `/admin/reports`
   ("Recently flagged irregularities") and can delete spam/duplicates. Reports
   and photos are also listed on the assignment page `/admin/assignments/{id}`.

Roles and access control live in `config/packages/security.yaml`:
`ROLE_ADMIN` scopes `/admin`, `ROLE_AGENT` scopes `/agent`, `ROLE_VOTER` scopes
`/profile`. Agents can only act on *their own* assignment (`AgentAssignmentVoter`,
attribute `AGENT_ASSIGNMENT`).

### 4. Civic content & engagement

- **Elections** (`/elections/{id}`): key dates, candidates (approval workflow
  under `/admin/elections/{id}/candidates`), registered-voter count, votes.
- **Parties** (`/parties/{id}`): manifesto, leadership announcement.
- **Polls / surveys** (`/polls`): open/closed, single-choice voting, results.
- **Forums** (`/forums`) with discussions, and **round tables** (`/round-tables`).
- The commission publishes official results per election
  (`/admin/elections/{id}/publish-results`), backed by service classes such as
  `ResultsService`.

## Main entry points

| Route | Purpose |
|-------|---------|
| `/` | Public home |
| `/register`, `/register/confirm` | Voter registration + OTP confirmation |
| `/login`, `/logout` | Voter / admin sign-in |
| `/profile` | Confirmed voter dashboard & election receipts |
| `/elections`, `/parties`, `/polls`, `/forums`, `/round-tables` | Public content |
| `/admin` | Commission back office (voters, elections, parties, agents, reports) |
| `/agent/login`, `/agent` | Party agent sign-in and dashboard |

## Project structure

```
config/packages/     # framework, doctrine, security, twig, validator, ...
migrations/          # doctrine migrations (never doctrine:schema:update)
src/
  Command/           # app:create-admin, app:seed-demo
  Controller/        # thin controllers; Admin/* for the back office
  Entity/            # Voter, Election, Party, Poll, AgentAssignment, ...
  Enum/              # VoterStatus, ElectionStatus, PartyAgentStatus, ...
  Form/              # server-rendered forms (ChoiceType, EnumType, Collections)
  Repository/        # query logic (Doctrine)
  Security/          # voters, token authenticators
  Service/           # VoterRegistrationService, OtpService, ObservationUploadService,
                     # CsvImportService, CodeGenerator, ResultsService, ...
templates/           # Twig (base layout incl. OpenGraph meta, page blocks)
  agent/             # party agent screens
  admin/             # back office screens
tests/Functional/    # end-to-end HTTP tests (WebTestCase)
public/              # web root; uploads/ for photo evidence
var/                 # runtime files: app.db (dev), logs, test.db
```

## Testing & code quality

```bash
php bin/phpunit                 # functional test suite (sqlite var/test.db, schema recreated per test)
php vendor/bin/php-cs-fixer fix # Symfony coding standard (@Symfony ruleset)
php bin/console lint:container  # validate service wiring
php bin/console lint:twig templates/
php bin/console doctrine:schema:validate
```

The test suite spins up a fresh in-memory-ish SQLite database
(`.env.test` → `var/test.db`) per test class, seeds `BaseWebTestCase` fixtures,
and drives real HTTP requests / form submissions (including agent login, report
uploads, admin flows, and CSV import). `tests/` then stays a smoke-level
contract of the main journeys.

## Configuration notes

- **Mailer**: `MAILER_DSN=null://null` by default so nothing is sent; the OTP is
  shown on the confirmation page instead. Point it at a real transport (e.g.
  `smtp://...`) to actually deliver email.
- **Database**: SQLite for local development. PostgreSQL line is provided as a
  commented example in `.env` for production.
- **CSRF** is stateless (Symfony 8.1): forms carry a `csrf-token` placeholder
  rendered into the token field. HTTP/fetch clients must send the `Origin`
  header when submitting form logins.
- **Concurrency** uses `symfony/lock` (e.g. `VotingService` guards a cast vote
  with key `vote:{election}:{voter}`) instead of custom flags.

## Further reading

- Symfony 8.1 docs: https://symfony.com/doc/8.1/
- Project conventions live in `AGENTS.md` (Autowiring, `#[Route]` attributes,
  `#[MapRequestPayload]`, migrations-first doctrine, testing expectations).