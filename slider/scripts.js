const slides = document.getElementById("slides");
const totalSlides = slides.children.length;
let index = 0;

function showSlide(i) {
    index = (i + totalSlides) % totalSlides;
    for (let j = 0; j < totalSlides; j++) {
        slides.children[j].classList.remove("active");
    }
    slides.children[index].classList.add("active");
}

function nextSlide() {
    showSlide(index + 1);
}

showSlide(index);

setInterval(nextSlide, 10000);
