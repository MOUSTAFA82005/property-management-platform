/**
 * The demo accounts created by DemoDataSeeder.
 *
 * The relationships matter: Youssef deals only with Nadia, while Salma and
 * Dina deal only with Hassan. That asymmetry is what makes the isolation
 * assertions meaningful — see isolation.spec.js.
 */
export const PASSWORD = 'password'

export const OWNERS = {
  hassan: { email: 'owner@propspace.com', name: 'Hassan Farouk' },
  nadia: { email: 'owner2@propspace.com', name: 'Nadia Mansour' },
}

export const CUSTOMERS = {
  omar: { email: 'customer@propspace.com', name: 'Omar Sabry' },
  salma: { email: 'customer2@propspace.com', name: 'Salma Adel' },
  youssef: { email: 'customer3@propspace.com', name: 'Youssef Ibrahim' },
  dina: { email: 'customer4@propspace.com', name: 'Dina Hafez' },
  karim: { email: 'customer5@propspace.com', name: 'Karim Nassar' },
}

/** Seeded properties, by owner. */
export const PROPERTIES = {
  nileView: { name: 'Nile View Residences', owner: 'hassan', published: true },
  palmGardens: { name: 'Palm Gardens Compound', owner: 'hassan', published: false },
  marina: { name: 'Alexandria Marina Towers', owner: 'nadia', published: true },
}
