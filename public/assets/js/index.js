new Swiper("#swiperEspecialidades", {
	slidesPerView: 1,
	spaceBetween: 20,
	loop: true,
	autoplay: { delay: 4000, disableOnInteraction: false },
	pagination: { el: ".swiper-pagination", clickable: true },
	navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" },
	breakpoints: {
		576: { slidesPerView: 2 },
		768: { slidesPerView: 3 },
		1200: { slidesPerView: 5 },
	},
});

(function () {
	var oc = document.getElementById("navMenu");
	var burger = document.getElementById("burgerBtn");
	if (!oc || !burger) return;
	oc.addEventListener("show.bs.offcanvas", function () {
		burger.classList.add("is-open");
		document.body.classList.add("menu-open");
		burger.setAttribute("aria-label", "Cerrar menú");
	});
	oc.addEventListener("hide.bs.offcanvas", function () {
		burger.classList.remove("is-open");
		document.body.classList.remove("menu-open");
		burger.setAttribute("aria-label", "Abrir menú");
	});
})();

