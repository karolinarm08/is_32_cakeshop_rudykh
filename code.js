// Получить кнопку
const scrollToTopBtn = document.getElementById('scrollToTopBtn');
    
// Показать кнопку, когда пользователь прокрутил вниз на 100 пикселей
window.onscroll = function () {
    if (document.body.scrollTop > 800 || document.documentElement.scrollTop > 800) {
        scrollToTopBtn.style.display = "block";
    } else {
        scrollToTopBtn.style.display = "none";
    }
};

// При нажатии на кнопку прокрутить вверх
scrollToTopBtn.onclick = function () {
    window.scrollTo({
        top: 0,
        behavior: 'smooth' // Плавная прокрутка
    });
};

function myFunction() {
    var x = document.getElementById("nav");
    if (x.className === "topnav") {
        x.className += " responsive";
    } else {
        x.className = "topnav";
    }
}

fetch('footer.html')
    .then(response => response.text())
    .then(html => {
        document.getElementById('footer').innerHTML = html;
    })
    .catch(error => console.error('Помилка завантаження фрагмента:', error));

function goToOrder() {
  var url = 'order.html';
  window.location.href = url;
}

