// Header Navbar & Profile Toggle
const navbar = document.querySelector('.header .flex .navbar');
const profile = document.querySelector('.header .flex .profile');
const menuBtn = document.querySelector('#menu-btn');
const userBtn = document.querySelector('#user-btn');

menuBtn?.addEventListener('click', () => {
   navbar?.classList.toggle('active');
   profile?.classList.remove('active');
});

userBtn?.addEventListener('click', () => {
   profile?.classList.toggle('active');
   navbar?.classList.remove('active');
});

// Remove toggles on page scroll
window.addEventListener('scroll', () => {
   navbar?.classList.remove('active');
   profile?.classList.remove('active');
});

// Product Gallery Image Switching (Quick View & Update Product)
const mainImage = document.querySelector('.quick-view .box .row .image-container .main-image img') 
               || document.querySelector('.update-product .image-container .main-image img');
const subImages = document.querySelectorAll('.quick-view .box .row .image-container .sub-image img, .update-product .image-container .sub-image img');

subImages.forEach(img => {
   img.addEventListener('click', () => {
      if (mainImage) {
         mainImage.src = img.getAttribute('src');
      }
   });
});