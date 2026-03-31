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

// Disable scroll immediately (only if loader exists)
if (load_block) {
  document.body.classList.add("no-scroll");
  setTimeout(() => {
    load_block.classList.add("clear");
    document.body.classList.remove("no-scroll");
  }, 2800);
}

// nav-bar
if (harm_btn && nav_bar && cover_bg) {
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
}

let url_locate = window.location.pathname;

nav_list.forEach((nav_li) => {
  let nav_url = nav_li.pathname;
  if (url_locate == nav_url) {
    nav_li.classList.add("nav_locate");
  }
});