class AudioPlayer {
    #audio = new Audio();

    constructor() {
        this.#audio.loop = true;
    }

    play(src) {
        this.#audio.src = src;
        this.#audio.currentTime = 0;
        this.#audio.play();
    }

    stop() {
        this.#audio.pause();
    }
}
