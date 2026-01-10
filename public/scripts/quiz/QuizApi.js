class QuizApi {
    async getQuizById(id) {
        const res = await fetch(`/api/quiz/details?id=${id}`);
        if (!res.ok) throw new Error('Quiz fetch failed');
        return res.json();
    }

    async quizFinish(dto) {
        const res = await fetch('/api/quiz/finish', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(dto)
        });

        if (!res.ok) throw new Error('Quiz finish failed');

        const html = await res.text();
        return html;
    }
}
