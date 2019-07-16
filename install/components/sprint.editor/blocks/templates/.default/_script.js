/* Общие скрипты для блоков */

/*accordion*/
document.addEventListener("DOMContentLoaded", function (e) {
    var acc = document.getElementsByClassName("sp-accordion");
    for (var accIndex = 0; accIndex < acc.length; accIndex++) {
        if (!acc[accIndex].classList.contains('sp-accordion__initialized')) {
            acc[accIndex].classList.add('sp-accordion__initialized');
            var titles = acc[accIndex].getElementsByClassName("sp-accordion-title");
            for (var titleIndex = 0; titleIndex < titles.length; titleIndex++) {
                titles[titleIndex].addEventListener("click", function () {
                    this.classList.toggle("sp-accordion-title__active");
                    var panel = this.nextElementSibling;
                    if (panel.style.display === "block") {
                        panel.style.display = "none";
                    } else {
                        panel.style.display = "block";
                    }
                });
            }
        }
    }
});

