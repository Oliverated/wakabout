let body = document.querySelector("body");
let modeBlk = document.getElementById("slider-ball");
let cover_bg = document.querySelector(".cover-bg");
let harm_btn = document.querySelector(".harm");
let nav_bar = document.querySelector("header nav");
let nav_list = document.querySelectorAll(".nav-list a");
let darkMode = localStorage.getItem("darkMode");
let load_block = document.querySelector(".loader-block");
let cancel_btn = document.querySelector(".cancel");
let sections = document.querySelectorAll("section");

// Disable scroll immediately
document.body.classList.add("no-scroll");

// Loader
setTimeout(() => {
  load_block.classList.add("clear");
  // Enable scroll after loader disappears
  document.body.classList.remove("no-scroll");
}, 2800);

// nav-bar

harm_btn.addEventListener("click", () => {
  nav_bar.classList.toggle("active");
  cover_bg.classList.toggle("active");
  harm_btn.classList.toggle("active");
});

cover_bg.addEventListener("click", () => {
  cover_bg.classList.remove("active");
  nav_bar.classList.remove("active");
  harm_btn.classList.remove("active");
});

nav_list.forEach((nav_li) => {
  nav_li.addEventListener("click", () => {
    cover_bg.classList.remove("active");
    nav_bar.classList.remove("active");
    harm_btn.classList.remove("active");
  });
});

window.onscroll = function () {
  cover_bg.classList.remove("active");
  nav_bar.classList.remove("active");
  harm_btn.classList.remove("active");
};

// let url_locate = window.location.pathname;

// nav_list.forEach((nav_li) => {
//   let nav_url = nav_li.pathname;
//   if (url_locate == nav_url) {
//     nav_li.classList.add("nav_locate");
//   }
// });

// slider js
    const slides = [
      {
        image: ". /kitchen-sink.jpg",
        title: "Breaking News: Dangote Refinery Once Again Raises Fuel Price to ₦1,275"
      },
      {
        image: "img/hero2.jpg",
        title: "Top 5 Stories Nigerians Are Talking About Today"
      },
      {
        image: "img/hero3.jpg",
        title: "Weekly Highlights: Everything You Missed"
      }
    ];

    let index = 0;
    const hero = document.querySelector(".hero");
    const title = document.getElementById("title");

    function showSlide() {
      hero.style.backgroundImage = `url(${slides[index].image})`;
      title.innerText = slides[index].title;
    }

    function nextSlide() {
      index = (index + 1) % slides.length;
      showSlide();
    }

    function prevSlide() {
      index = (index - 1 + slides.length) % slides.length;
      showSlide();
    }

    // Initial load
    showSlide();

// FAQ Accordion
const faqItems = document.querySelectorAll(".faq-item");

faqItems.forEach((item) => {
  const question = item.querySelector(".faq-question");
  question.addEventListener("click", () => {
    // Close other items
    faqItems.forEach((otherItem) => {
      if (otherItem !== item) {
        otherItem.classList.remove("active");
      }
    });

    // Toggle current item
    item.classList.toggle("active");
  });
});

// contact form - whatsapp integration

document.getElementById("whatsappForm").addEventListener("submit", function(e) {
    e.preventDefault();

    let name = document.getElementById("name").value;
    let number = document.getElementById("number").value;
    let message = document.getElementById("message").value;

    let phone = "2347066071996"; // your WhatsApp number

    let text = `Hello, my name is ${name}.
My number is ${number}.
${message}`;

    let encodedText = encodeURIComponent(text);

    let url = `https://wa.me/${phone}?text=${encodedText}`;

    window.open(url, "_blank");
});

