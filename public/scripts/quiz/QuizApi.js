class QuizApi {
    async getQuizById(id) {
        const res = await fetch(`/api/quiz/details?id=${id}`);
        if (!res.ok) throw new Error('Quiz fetch failed');
        return res.json();
    }
}
