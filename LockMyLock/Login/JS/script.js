const container = document.getElementById('container');
const registerBtn = document.getElementById('register');
const loginBtn = document.getElementById('login');

registerBtn.addEventListener('click', () => {
    container.classList.add("active");
});

loginBtn.addEventListener('click', ()=> {
    container.classList.remove("active");
});


const registerForm = document.querySelector('.form-container.sign-up form');
const loginForm = document.querySelector('.form-container.sign-in form');

function disableAutocomplete(form) {
    const formElements = form.elements;
    for (let i = 0; i < formElements.length; i++) {
        const element = formElements[i];
        if (element.tagName.toLowerCase() === 'input') {
            element.autocomplete = 'off';
        }
    }
}

disableAutocomplete(registerForm);
disableAutocomplete(loginForm);
