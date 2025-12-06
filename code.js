const scrollToTopBtn = document.getElementById('scrollToTopBtn');
    
window.onscroll = function () {
    if (document.body.scrollTop > 800 || document.documentElement.scrollTop > 800) {
        scrollToTopBtn.style.display = "block";
    } else {
        scrollToTopBtn.style.display = "none";
    }
};

scrollToTopBtn.onclick = function () {
    window.scrollTo({
        top: 0,
        behavior: 'smooth' 
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

