class QuizTimer {
    #interval;

    start(duration, onTick, onEnd) {
        let timeLeft = duration;

        onTick(timeLeft);

        this.#interval = setInterval(() => {
            timeLeft--;
            onTick(timeLeft);

            if (timeLeft <= 0) {
                this.stop();
                onEnd();
            }
        }, 1000);
    }

    stop() {
        clearInterval(this.#interval);
    }
}
