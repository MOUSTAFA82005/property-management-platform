/**
 * The demo accounts created by DemoDataSeeder.
 *
 * PropSpace has exactly one owner. Every customer below reaches him through a
 * contract or a purchase request, except Nour, who has only ever enquired.
 */
export const PASSWORD = 'password'

export const OWNERS = {
  hassan: { email: 'owner@propspace.com', name: 'Hassan Farouk' },
}

/** The single owner, for the many specs that only ever need "the owner". */
export const OWNER = OWNERS.hassan

export const CUSTOMERS = {
  omar: { email: 'customer@propspace.com', name: 'Omar Sabry' },
  salma: { email: 'customer2@propspace.com', name: 'Salma Adel' },
  youssef: { email: 'customer3@propspace.com', name: 'Youssef Ibrahim' },
  dina: { email: 'customer4@propspace.com', name: 'Dina Hafez' },
  karim: { email: 'customer5@propspace.com', name: 'Karim Nassar' },
  nour: { email: 'customer6@propspace.com', name: 'Nour Khalil' },
}

/** Seeded properties — all three belong to the one owner. */
export const PROPERTIES = {
  nileView: { name: 'Nile View Residences', owner: 'hassan', published: true },
  palmGardens: { name: 'Palm Gardens Compound', owner: 'hassan', published: false },
  marina: { name: 'Alexandria Marina Towers', owner: 'hassan', published: true },
}
