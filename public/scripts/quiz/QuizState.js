class QuizState {
    score = 0;
    currentQuestion = 0;
    correctAnswers = 0;
    incorrectAnswers = 0;
    takenTime = 0;

    addScore(timeLeft) {
        this.score += timeLeft * QuizConfig.SCORE_MULTIPLIER;
    }

    nextQuestion() {
        this.currentQuestion++;
    }

    addCorrectAnswer() {
        this.correctAnswers++;
    }

    addIncorrectAnswer() {
        this.incorrectAnswers++;
    }

    addSecondToTime() {
        this.takenTime++;
    }
}
