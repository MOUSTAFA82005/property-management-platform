# PropSpace — UML Documentation

Every diagram here was written from the code, not from a design document.
Each file names the classes and files it was derived from, so a diagram can be
re-checked against the implementation it describes.

All diagrams are **Mermaid** source inside Markdown: they render on GitHub and
stay editable in any text editor. No screenshots, no binary sources.

## Contents

| Diagram | File | What it answers |
|---------|------|-----------------|
| Use case | [`use-case.md`](./use-case.md) | Who the actors are and what each can do |
| Class diagram | [`class-diagram.md`](./class-diagram.md) | The backend domain model and its relationships |
| Authentication & authorization | [`authentication-authorization.md`](./authentication-authorization.md) | How a request is identified and what is allowed to touch it |
| Sequence — customer login | [`login-sequence.md`](./login-sequence.md) | Browser → Vue → Axios → Laravel → token → auth state |
| Sequence — create contract | [`contract-create-sequence.md`](./contract-create-sequence.md) | Owner writes a lease and the unit becomes occupied |
| Sequence — edit contract | [`contract-edit-sequence.md`](./contract-edit-sequence.md) | Unit reassignment, old-unit release, transaction |
| Sequence — delete contract | [`contract-delete-sequence.md`](./contract-delete-sequence.md) | Payment dependency check, unit release, 204 |
| Sequence — notifications | [`notification-sequence.md`](./notification-sequence.md) | How a notification is raised, stored, polled and read |
| Activity — request to payment | [`activity-diagram.md`](./activity-diagram.md) | The end-to-end business flow across both roles |
| State machines | [`state-diagram.md`](./state-diagram.md) | Unit, contract, payment and purchase-request statuses |

## Scope note

PropSpace has exactly **two actors: Owner and Customer** (plus an unauthenticated
Visitor who can browse the public catalog). There is **no Admin actor** and
**no maintenance module** anywhere in the codebase — neither appears in any
diagram here.

Related documentation: [API reference](../API/README.md) · [ERD](../ERD/README.md)
