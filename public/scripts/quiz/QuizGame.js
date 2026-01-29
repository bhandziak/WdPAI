class QuizGame {
    constructor(api, ui, audio, timer, state, quizId) {
        this.api = api;
        this.ui = ui;
        this.audio = audio;
        this.timer = timer;
        this.state = state;
        this.quiz = null;
        this.quizId = quizId;
    }

    async start() {
        const quizId = this.quizId;

        try {
            this.quiz = await this.api.getQuizById(quizId);
        } catch (error) {
            alert(error.message);
            window.location.href = '/home';
        }
        console.log(this.quiz);

        this.setUpUI();

        this.playCurrentQuestion();
    }

    setUpUI() {
        if (this.quiz.album_cover_url) {
            const albumCoverUrl = QuizConfig.SERVER_DIR + this.quiz.album_cover_url;
            this.ui.setAlbumCover(albumCoverUrl);
        }

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
        const audioUrl = QuizConfig.SERVER_DIR + question.audio_url
        this.audio.play(audioUrl);

        // timer 
        this.timer.start(
            QuizConfig.TIME_LIMIT,
            timeLeft => {
                this.state.addSecondToTime();
                this.ui.updateTimer(timeLeft, QuizConfig.TIME_LIMIT);
            },
            () => this.handleAnswer(false, question)
        );
    }

    handleAnswer(isCorrect, question, clickedIndex = -1) {
        this.timer.stop();

        // get info
        const correctAnswerIndex = question.answers.findIndex(a => a.is_correct === true);

        const timeLeft = parseInt(this.ui.getTimerEl().textContent ?? 0);

        // add score
        if (isCorrect) {
            this.state.addScore(timeLeft);
            this.state.addCorrectAnswer();
            this.ui.updateScore(this.state.score);
        } else {
            this.state.addIncorrectAnswer();
        }

        // highlight btns
        this.ui.highlightAnswers(correctAnswerIndex, clickedIndex);

        // no answer - timeout
        setTimeout(() => {
            this.state.nextQuestion();
            this.playCurrentQuestion();
        }, QuizConfig.ANSWER_DELAY);
    }

    async endQuiz() {
        this.audio.stop();

        const score = this.state.score;
        const total_time = this.state.takenTime;
        const correct_answers = this.state.correctAnswers;
        const incorrect_answers = this.state.incorrectAnswers;
        const quiz_id = this.quizId;

        const dto = {
            quiz_id,
            score,
            total_time,
            correct_answers,
            incorrect_answers
        }

        try {
            const quizResultHtml = await this.api.quizFinish(dto);
            document.documentElement.innerHTML = quizResultHtml;
        } catch (err) {
            console.error(err);
        }
    }
}
