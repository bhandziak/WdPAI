class QuizState {
    score = 0;
    currentQuestion = 0;

    addScore(timeLeft) {
        this.score += timeLeft * QuizConfig.SCORE_MULTIPLIER;
    }

    nextQuestion() {
        this.currentQuestion++;
    }
}
