// get quizId
const quizId = new URLSearchParams(window.location.search).get('id');

if (!quizId) {
    alert("Bad URL");
    window.location.href = '/home';
}

const api = new QuizApi();
const ui = new QuizUI();
const audio = new AudioPlayer();
const timer = new QuizTimer();
const state = new QuizState();

const game = new QuizGame(api, ui, audio, timer, state, quizId);

game.start();
