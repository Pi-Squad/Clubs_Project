const container = document.getElementById("container");
const registerBtn = document.getElementById("register");
const loginBtn = document.getElementById("login");

registerBtn.addEventListener("click", () => {
    container.classList.add("active");
});

loginBtn.addEventListener("click", () => {
    container.classList.remove("active");
});

setTimeout(() => {
    document.querySelectorAll('.message-erreur').forEach(el => {
        el.style.display = 'none';
    });
}, 5000);

