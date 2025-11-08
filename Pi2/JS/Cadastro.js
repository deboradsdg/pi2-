const boxLogin = document.querySelector('.box');
const boxCadastro = document.querySelector('.box2');
const toCadastro = document.getElementById('toCadastro');
const toLogin = document.querySelector('.box2 .but');

toCadastro.addEventListener('click', () => {
  boxLogin.classList.replace('box', 'box2');
  boxCadastro.classList.replace('box2', 'box');
});

// Quando clicar em "Já possui uma conta?"
toLogin.addEventListener('click', () => {
  boxCadastro.classList.replace('box', 'box2');
  boxLogin.classList.replace('box2', 'box');
});
