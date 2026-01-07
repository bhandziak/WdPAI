class QuizUI {
    // score and question nr
    #scoreEl;
    #questionNrEl;
    #questionBarFill;
    // album cover
    #quizNameEl;
    #albumCoverUrl;
    // audio
    #audioBarEl;
    #playerTimeStart;
    #playerTimeEnd;
    #timerEl
    // question
    #questionTextEl;
    // answers
    #answersEl;

    constructor() {
        this.#scoreEl = document.querySelector('.quiz-score-number');
        this.#questionNrEl = document.querySelector('.question-current-nr');
        this.#questionBarFill = document.querySelector('.progress-fill');

        this.#albumCoverUrl = document.querySelector('.album-art');
        this.#quizNameEl = document.querySelector('.quiz-name')

        this.#audioBarEl = document.querySelector('.audio-bar');
        this.#playerTimeStart = document.querySelector('.time-start-text');
        this.#playerTimeEnd = document.querySelector('.time-end-text');
        this.#timerEl = document.querySelector('.timer-text');

        this.#questionTextEl = document.querySelector('.question');
        this.#answersEl = document.querySelector('.answers');
    }

    /* ---------- SCORE & PROGRESS ---------- */

    updateScore(score) {
        this.#scoreEl.textContent = score;
    }

    updateQuestionNr(current, total) {
        this.#questionNrEl.textContent = `Question ${current}/${total}`;
        this.#questionBarFill.style.width = `${(current / total) * 100}%`;
    }

    /* ---------- ALBUM ---------- */

    setQuizName(name) {
        this.#quizNameEl.textContent = name;
    }

    setAlbumCover(url) {
        this.#albumCoverUrl.style.backgroundImage = `url(${url})`;
    }

    /* ---------- QUESTION ---------- */

    setQuestion(text) {
        this.#questionTextEl.textContent = text;
    }

    /* ---------- ANSWERS ---------- */

    setAnswers(answers, onAnswer) {
        this.#answersEl.innerHTML = '';

        answers.forEach((answer, index) => {
            const btn = document.createElement('button');
            btn.classList.add('answer');
            btn.textContent = answer.text;

            btn.addEventListener('click', () => {
                onAnswer(answer.isCorrect, index);
            });

            this.#answersEl.appendChild(btn);
        });
    }

    highlightAnswers(correctIndex, clickedIndex = -1) {
        console.log("highlightAnswers - correctIndex", correctIndex);

        [...this.#answersEl.children].forEach((btn, index) => {
            btn.disabled = true;

            if (index === correctIndex) btn.classList.add('correct');

            const clickedAtWrongAnswer = clickedIndex >= 0 && index === clickedIndex && clickedIndex !== correctIndex;
            if (clickedAtWrongAnswer) btn.classList.add('wrong');
        });
    }

    /* ---------- TIMER & AUDIO BAR ---------- */

    initTimer(totalTime) {
        this.#audioBarEl.max = totalTime;
        this.#audioBarEl.value = 0;

        this.#playerTimeStart.textContent = '0:00';
        this.#playerTimeEnd.textContent = `0:${totalTime.toString().padStart(2, '0')}`;
    }

    updateTimer(timeLeft, totalTime) {
        this.#timerEl.textContent = timeLeft;
        this.#audioBarEl.value = totalTime - timeLeft;

        const elapsed = totalTime - timeLeft;
        const seconds = elapsed % 60;
        this.#playerTimeStart.textContent = `0:${seconds.toString().padStart(2, '0')}`;
    }

    getTimerEl() {
        return this.#timerEl;
    }
}