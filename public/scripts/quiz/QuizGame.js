class QuizGame {
    constructor(api, ui, audio, timer, state) {
        this.api = api;
        this.ui = ui;
        this.audio = audio;
        this.timer = timer;
        this.state = state;
        this.quiz = null;
    }

    async start(quizId) {
        this.quiz = await this.api.getQuizById(quizId);

        this.setUpUI();

        this.playCurrentQuestion();
    }

    setUpUI() {
        const albumCoverUrl = QuizConfig.SERVER_DIR + this.quiz.albumCoverUrl;
        this.ui.setAlbumCover(albumCoverUrl);

        this.ui.setQuizName(this.quiz.title);
    }

    playCurrentQuestion() {
        // prepare question
        const index = this.state.currentQuestion;
        const question = this.quiz.questions[index];

        // on quiz finish
        if (!question) {
            this.endQuiz();
            return;
        }

        // set UI text
        this.ui.setQuestion(question.text);
        this.ui.updateQuestionNr(index + 1, this.quiz.questions.length);

        // set UI timer
        this.ui.initTimer(QuizConfig.TIME_LIMIT);

        // set event for answer btn
        this.ui.setAnswers(
            question.answers,
            (isCorrect, clickedIndex) => this.handleAnswer(isCorrect, question, clickedIndex)
        );


        // audio
        const audioUrl = QuizConfig.SERVER_DIR + question.audioUrl
        this.audio.play(audioUrl);

        // timer 
        this.timer.start(
            QuizConfig.TIME_LIMIT,
            timeLeft => this.ui.updateTimer(timeLeft, QuizConfig.TIME_LIMIT),
            () => this.handleAnswer(false, question)
        );
    }

    handleAnswer(isCorrect, question, clickedIndex = -1) {
        this.timer.stop();

        // get info
        const correctAnswerIndex = question.answers.findIndex(a => a.isCorrect === true);

        const timeLeft = parseInt(this.ui.getTimerEl().textContent ?? 0);

        // add score
        if (isCorrect) {
            this.state.addScore(timeLeft);
            this.ui.updateScore(this.state.score);
        }

        // highlight btns
        this.ui.highlightAnswers(correctAnswerIndex, clickedIndex);

        // no answer - timeout
        setTimeout(() => {
            this.state.nextQuestion();
            this.playCurrentQuestion();
        }, QuizConfig.ANSWER_DELAY);
    }

    endQuiz() {
        this.audio.stop();
        console.log("Score: " + this.state.score);
    }
}
