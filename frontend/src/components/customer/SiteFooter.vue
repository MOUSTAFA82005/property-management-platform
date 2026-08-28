<script setup>
import { RouterLink } from 'vue-router'
</script>

<template>
  <footer class="site-footer" id="footer">
    <div class="footer-container">
      <!-- Top section -->
      <div class="footer-top">
        <!-- Brand -->
        <div class="footer-brand">
          <RouterLink to="/" class="footer-logo">
            <svg width="30" height="28" viewBox="0 0 48 46" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path fill="#5B3FE0" d="M25.946 44.938c-.664.845-2.021.375-2.021-.698V33.937a2.26 2.26 0 0 0-2.262-2.262H10.287c-.92 0-1.456-1.04-.92-1.788l7.48-10.471c1.07-1.497 0-3.578-1.842-3.578H1.237c-.92 0-1.456-1.04-.92-1.788L10.013.474c.214-.297.556-.474.92-.474h28.894c.92 0 1.456 1.04.92 1.788l-7.48 10.471c-1.07 1.498 0 3.579 1.842 3.579h11.377c.943 0 1.473 1.088.89 1.83L25.947 44.94z"/>
            </svg>
            <span class="footer-brand-name">PropSpace</span>
          </RouterLink>
          <p class="footer-tagline">
            A trusted platform connecting property owners with customers across Egypt.
          </p>
        </div>

        <!-- Quick Links -->
        <div class="footer-links-group">
          <h4 class="footer-group-title">Quick Links</h4>
          <ul class="footer-links">
            <li><RouterLink to="/" class="footer-link">Home</RouterLink></li>
            <li><RouterLink to="/properties" class="footer-link">Properties</RouterLink></li>
            <li><a href="#why-us" class="footer-link">About</a></li>
            <li><a href="#footer" class="footer-link">Contact</a></li>
          </ul>
        </div>

        <!-- Account -->
        <div class="footer-links-group">
          <h4 class="footer-group-title">Account</h4>
          <ul class="footer-links">
            <li><RouterLink to="/login" class="footer-link">Login</RouterLink></li>
            <li><RouterLink to="/register" class="footer-link">Register</RouterLink></li>
          </ul>
        </div>

        <!-- Contact -->
        <div class="footer-links-group">
          <h4 class="footer-group-title">Contact</h4>
          <ul class="footer-links">
            <li class="footer-contact-item">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M3 8l7.89 5.26a2 2 0 0 0 2.22 0L21 8M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              info@propspace.com
            </li>
            <li class="footer-contact-item">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.88 12a19.79 19.79 0 0 1-3.07-8.63A2 2 0 0 1 3.77 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.06 6.06l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              +20 10 1234 5678
            </li>
            <li class="footer-contact-item">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="9" r="2.5" stroke="currentColor" stroke-width="2"/></svg>
              Cairo, Egypt
            </li>
          </ul>
        </div>
      </div>

      <!-- Bottom -->
      <div class="footer-bottom">
        <p class="footer-copy">
          &copy; {{ new Date().getFullYear() }} PropSpace. All rights reserved.
        </p>
        <div class="footer-bottom-links">
          <a href="#" class="footer-bottom-link">Privacy Policy</a>
          <span class="separator">·</span>
          <a href="#" class="footer-bottom-link">Terms of Service</a>
        </div>
      </div>
    </div>
  </footer>
</template>

<style scoped>
.site-footer {
  background: #14141F;
  color: #94a3b8;
  padding: 4rem 1.5rem 2rem;
}

.footer-container {
  max-width: 1280px;
  margin: 0 auto;
}

/* Top grid */
.footer-top {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1.5fr;
  gap: 3rem;
  margin-bottom: 3rem;
  padding-bottom: 3rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.07);
}

/* Brand */
.footer-logo {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  text-decoration: none;
  margin-bottom: 1rem;
}

.footer-brand-name {
  font-size: 1.2rem;
  font-weight: 700;
  color: #fff;
  letter-spacing: -0.02em;
}

.footer-tagline {
  font-size: 0.875rem;
  color: #64748b;
  line-height: 1.65;
  max-width: 280px;
}

/* Links */
.footer-group-title {
  font-size: 0.8rem;
  font-weight: 700;
  color: #fff;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  margin-bottom: 1.25rem;
}

.footer-links {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
}

.footer-link {
  font-size: 0.875rem;
  color: #64748b;
  text-decoration: none;
  transition: color 0.2s;
}

.footer-link:hover {
  color: #BEADFA;
}

/* Contact items */
.footer-contact-item {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #64748b;
  line-height: 1.5;
}

.footer-contact-item svg {
  flex-shrink: 0;
  margin-top: 2px;
  color: #5B3FE0;
}

/* Bottom */
.footer-bottom {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1rem;
}

.footer-copy {
  font-size: 0.825rem;
  color: #475569;
  margin: 0;
}

.footer-bottom-links {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.footer-bottom-link {
  font-size: 0.825rem;
  color: #475569;
  text-decoration: none;
  transition: color 0.2s;
}

.footer-bottom-link:hover {
  color: #BEADFA;
}

.separator {
  color: #334155;
}

@media (max-width: 900px) {
  .footer-top {
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
  }

  .footer-brand {
    grid-column: 1 / -1;
  }
}

@media (max-width: 560px) {
  .footer-top {
    grid-template-columns: 1fr;
  }

  .footer-brand {
    grid-column: 1;
  }

  .footer-bottom {
    flex-direction: column;
    text-align: center;
  }

  .site-footer {
    padding: 3rem 1rem 1.5rem;
  }
}
</style>
