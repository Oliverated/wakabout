<?php
require_once 'session_config.php';
require_once 'db.php';
?>
<footer>
  <div class="footer-content">
    <div class="footer-section about">
      <h3>About Us</h3>
      <p>
        Wakaabout is a travel and tourism blog dedicated to providing readers
        with the latest news, insights, and tips on travel destinations,
        experiences, and trends. Our mission is to inspire and empower
        travelers to explore the world and create unforgettable memories
        <span>
          Nigeria's original travel voice <em> since 2010. </em>  
        </span>
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
    <div class="footer-section contact social-title-block">
      <h3 class="social-title">Follow Us</h3>
      <div class="socials">
        <a href="https://youtube.com/@wakaabouttv?si=uXb490A64pUSemho" title="YouTube">
          <svg fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd"
              d="M21.7 8c-.2-1.5-1.4-2.7-2.9-2.9C16.2 4.8 12 4.8 12 4.8s-4.2 0-6.8.3C3.7 5.3 2.5 6.5 2.3 8 2 10.7 2 12 2 12s0 1.3.3 4c.2 1.5 1.4 2.7 2.9 2.9 2.6.3 6.8.3 6.8.3s4.2 0 6.8-.3c1.5-.2 2.7-1.4 2.9-2.9.3-2.7.3-4 .3-4s0-1.3-.3-4zM10 15V9l5.2 3L10 15z"
              clip-rule="evenodd"></path>
          </svg>
        </a>

      </div>
      <form class="sub-form" id="newsletterForm">
        <h5>Subscribe to our newsletter</h5>
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
    <p>&copy; 2026 Wakaabout Online. All rights reserved</p>
<div class="creator-block">Powered by: <a href="ratedcodes.vercel.app">Ratedcodes</a></div>
  </div>
</footer>
</body>

</html>