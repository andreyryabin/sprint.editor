/* Общие скрипты для блоков */

/*accordion*/
document.addEventListener("DOMContentLoaded", function (e) {
    let titles = document.getElementsByClassName("sp-accordion-title");
    for (let titleIndex = 0; titleIndex < titles.length; titleIndex++) {
        if (!titles[titleIndex].classList.contains('sp-accordion__initialized')) {
            titles[titleIndex].classList.add('sp-accordion__initialized');
            titles[titleIndex].addEventListener("click", function () {
                let panel = this.nextElementSibling;
                if (panel.style.display === "block") {
                    this.classList.remove("sp-accordion-title__active");
                    panel.style.display = "none";
                } else {
                    this.classList.add("sp-accordion-title__active");
                    panel.style.display = "block";
                }
            });
        }
    }
});

