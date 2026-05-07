<?php
$pageTitle = 'Contact Us';
$extraCss = 'pages.css';
$pageDescription = 'Get in touch with the Wakabout team. We\'d love to hear from you!';
require_once 'includes/header.php';
?>

<section class="elephant-sector">
  <h2 class="elephant-title">Contact Us</h2>
  <div class="elephant-nav"><a href="index.html">Home</a> | <span>Contact</span></div>
</section>

<div class="contact-main">
  <div class="contact-grid">

    <!-- Contact Form -->
    <div class="contact-form">
      <h2>Send Us a Message</h2>
      <form id="whatsappForm">
        <div class="form-group">
          <!-- <label for="name">Full Name</label> -->
          <input type="text" id="name" name="name" placeholder="Enter your full name" required>
        </div>
        <div class="form-group">
          <!-- <label for="number">Phone Number</label> -->
          <input type="email" id="email" name="email" placeholder="Enter your email" required>
        </div>
        <div class="form-group">
          <!-- <label for="message">Message</label> -->
          <textarea id="message" name="message" placeholder="Type your message here..." required></textarea>
        </div>
        <button type="submit" class="submit-btn">Send via WhatsApp</button>
      </form>
    </div>

    <!-- Contact Details -->
    <div class="contact-details">
      <h2>Get In Touch</h2>

      <div class="contact-item">
        <div class="contact-icon">
          <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M6.492 2.25c-.393 0-.78.14-1.102.398l-.047.024-.023.023L2.976 5.11 3 5.133a2.35 2.35 0 0 0-.633 2.531c.003.006-.003.018 0 .024.636 1.819 2.262 5.332 5.437 8.507 3.188 3.188 6.747 4.75 8.508 5.438h.024a2.692 2.692 0 0 0 2.601-.516l2.367-2.367c.622-.621.622-1.7 0-2.32l-3.046-3.047-.024-.047c-.621-.621-1.723-.621-2.344 0l-1.5 1.5a12.131 12.131 0 0 1-3.07-2.11c-1.228-1.171-1.854-2.519-2.086-3.046l1.5-1.5c.63-.63.642-1.679-.023-2.297l.023-.024-.07-.07-3-3.094-.024-.023-.047-.024a1.767 1.767 0 0 0-1.101-.398Z">
            </path>
          </svg>
        </div>
        <div>
          <h6>Phone</h6>
          <a href="tel:+2347066071996">+234 706 607 1996</a>
        </div>
      </div>

      <div class="contact-item">
        <div class="contact-icon">
          <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z">
            </path>
          </svg>
        </div>
        <div>
          <h6>Email</h6>
          <a href="mailto:hello@wakabout.com">hello@wakabout.com</a>
        </div>
      </div>

      <div class="contact-item">
        <div class="contact-icon">
          <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 0 1 0-5 2.5 2.5 0 0 1 0 5z">
            </path>
          </svg>
        </div>
        <div>
          <h6>Location</h6>
          <p>Benin City, Edo State, Nigeria</p>
        </div>
      </div>


      <!-- Social Links -->
      <div class="contact-social-block">
        <h5>Follow Us</h5>
        <div class="author-social">
          <a href="https://www.instagram.com/peluawofeso?utm_source=qr&igsh=MW8zbTNzcjNtbGxzZw==" title="Instagram">
            <svg fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path
                d="M16.375 3.25a4.388 4.388 0 0 1 4.375 4.375v8.75a4.388 4.388 0 0 1-4.375 4.375h-8.75a4.389 4.389 0 0 1-4.375-4.375v-8.75A4.388 4.388 0 0 1 7.625 3.25h8.75Zm0-1.75h-8.75C4.256 1.5 1.5 4.256 1.5 7.625v8.75c0 3.369 2.756 6.125 6.125 6.125h8.75c3.369 0 6.125-2.756 6.125-6.125v-8.75c0-3.369-2.756-6.125-6.125-6.125Z">
              </path>
              <path d="M17.688 7.625a1.313 1.313 0 1 1 0-2.625 1.313 1.313 0 0 1 0 2.625Z"></path>
              <path
                d="M12 8.5a3.5 3.5 0 1 1 0 7 3.5 3.5 0 0 1 0-7Zm0-1.75a5.25 5.25 0 1 0 0 10.5 5.25 5.25 0 0 0 0-10.5Z">
              </path>
            </svg>
          </a>

          <a href="https://www.facebook.com/share/1ChRAPRkst/" title="Facebook">
            <svg class="icons" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd"
                d="M22.5 12.063c0-5.799-4.702-10.5-10.5-10.5s-10.5 4.7-10.5 10.5c0 5.24 3.84 9.584 8.86 10.373v-7.337H7.692v-3.037h2.666V9.75c0-2.63 1.568-4.085 3.966-4.085 1.15 0 2.351.205 2.351.205v2.584h-1.324c-1.304 0-1.712.81-1.712 1.64v1.97h2.912l-.465 3.036H13.64v7.337c5.02-.788 8.859-5.131 8.859-10.373Z"
                clip-rule="evenodd"></path>
            </svg>
          </a>
          <a style="" href="https://youtube.com/@wakaabouttv?si=uXb490A64pUSemho"
            title="YouTube">
            <svg fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd"
                d="M21.7 8c-.2-1.5-1.4-2.7-2.9-2.9C16.2 4.8 12 4.8 12 4.8s-4.2 0-6.8.3C3.7 5.3 2.5 6.5 2.3 8 2 10.7 2 12 2 12s0 1.3.3 4c.2 1.5 1.4 2.7 2.9 2.9 2.6.3 6.8.3 6.8.3s4.2 0 6.8-.3c1.5-.2 2.7-1.4 2.9-2.9.3-2.7.3-4 .3-4s0-1.3-.3-4zM10 15V9l5.2 3L10 15z"
                clip-rule="evenodd"></path>
            </svg>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>