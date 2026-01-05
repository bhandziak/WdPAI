const input = document.querySelector('.search-input');
const container = document.querySelector('.quizzes-container');

input.addEventListener('input', async () => {
    const value = input.value.trim();

    const res = await fetch(`/api/quizzes?search=${encodeURIComponent(value)}`);
    const quizzes = await res.json();
    const numberOfQuizzes = quizzes.length;

    if (numberOfQuizzes === 0) {
        container.innerHTML = `No quizzes found: ${value} ...`;
        return;
    }

    container.innerHTML = '';
    quizzes.forEach(q => {
        const tile = document.createElement('div');
        tile.className = 'quiz-tile';

        tile.innerHTML = `
            <img class="quiz-tile-album-cover" src="${q.albumcoverurl ?? ''}" />
            <div class="quiz-tile-title">${q.title}</div>
            <div class="quiz-tile-subtitle">by ${q.createdbyusername}</div>
        `;

        container.appendChild(tile);
    });
});