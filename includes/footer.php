<?php
require_once 'session_config.php';
require_once 'db.php';
?>
<footer>
  <div class="footer-content">
    <div class="footer-section about">
      <h3>About Us</h3>
      <p>
        Wakabout is a travel and tourism blog dedicated to providing readers
        with the latest news, insights, and tips on travel destinations,
        experiences, and trends. Our mission is to inspire and empower
        travelers to explore the world and create unforgettable memories.
      </p>
      <a class="footer-btn" href="https://wa.me/12345678910">
        Contact Us Now
      </a>
    </div>
    <div class="footer-section links">
      <h3>Quick Links</h3>
      <ul>
        <li><a href="blog.php">Breaking News</a></li>
        <li><a href="books.php">Lastest Books</a></li>
        <li><a href="events.php">Upcomming Events</a></li>
        <li><a href="about.php">About Me</a></li>
<?php
if (isset($_SESSION['user_id'])) {
    echo "<a class=logout-btn href=./auth/logout.php>Logout</a> ";
} else{
  echo "<li><a href=auth/login.php>Login Your Account</a></li>" ;
}
  
?>
    
      </ul>
    </div>
    <div class="footer-section contact">
      <h3>Follow Us</h3>
      <div class="socials">
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
        <a href="https://youtube.com/@wakaabouttv?si=uXb490A64pUSemho" title="YouTube">
          <svg fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd"
              d="M21.7 8c-.2-1.5-1.4-2.7-2.9-2.9C16.2 4.8 12 4.8 12 4.8s-4.2 0-6.8.3C3.7 5.3 2.5 6.5 2.3 8 2 10.7 2 12 2 12s0 1.3.3 4c.2 1.5 1.4 2.7 2.9 2.9 2.6.3 6.8.3 6.8.3s4.2 0 6.8-.3c1.5-.2 2.7-1.4 2.9-2.9.3-2.7.3-4 .3-4s0-1.3-.3-4zM10 15V9l5.2 3L10 15z"
              clip-rule="evenodd"></path>
          </svg>
        </a>

      </div>
      <form class="sub-form" id="newsletterForm">
        <h5>Subscribe to our news letter</h5>
        <div class="sub-inp-block">
          <input placeholder="Enter Email" type="email" id="nlEmail" name="email" required>
          <button type="submit" id="nlBtn">subscribe</button>
        </div>
        <p id="nl-message" style="margin-bottom:10px;font-size:14px;display:none;"></p>
      </form>
           <a href="#"><img class="footer-logo" src="./assets/public/footerlogo.png" alt="Wakabout Logo" /></a> 
      <script>
        const nlForm = document.getElementById('newsletterForm');
        if (nlForm) {
          nlForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('nlBtn');
            const msg = document.getElementById('nl-message');
            const input = document.getElementById('nlEmail');

            btn.disabled = true;
            btn.textContent = 'Wait...';
            msg.style.display = 'none';

            try {
              const fd = new FormData();
              fd.append('email', input.value);

              // We infer path depth by checking if we are in admin/ or root
              const isRoot = window.location.pathname.includes('/auth') || window.location.pathname.includes('/admin') ? false : true;
              const apiUrl = isRoot ? 'ajax/subscribe.php' : '../ajax/subscribe.php';

              const res = await fetch(apiUrl, {
                method: 'POST',
                body: fd
              });
              const data = await res.json();

              msg.textContent = data.message;
              msg.style.display = 'block';
              msg.style.color = data.success ? '#10b981' : '#ef4444'; // green vs red

              if (data.success) input.value = '';
                          btn.textContent = 'subscribed';
            } catch (err) {
              msg.textContent = 'An error occurred. Please try again.';
              msg.style.color = '#ef4444';
              msg.style.display = 'block';
            }

            btn.disabled = false;
            btn.textContent = 'subscribed';
          });
        }
      </script>
    </div>
  </div>
  <div class="footer-bottom">
    <p>&copy; 2026 Wakabout. All rights reserved</p>
<div class="creator-block">Powered by: <a href="ratedcodes.vercel.app">Ratedcodes</a></div>
  </div>
</footer>
</body>

</html>