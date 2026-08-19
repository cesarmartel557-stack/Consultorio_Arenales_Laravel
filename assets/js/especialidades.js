AOS.init({
	duration: 600,
	easing: "ease-out",
	once: false,
	mirror: true,
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

