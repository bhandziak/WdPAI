const quizId = new URLSearchParams(window.location.search).get('id');
const quizForm = document.querySelector('.quiz-form');
const quizContainer = document.getElementById('quiz-form-body');

const editQuiz = new EditQuiz(quizForm, quizContainer, quizId);
editQuiz.init();