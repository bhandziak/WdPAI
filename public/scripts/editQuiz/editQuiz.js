class EditQuiz {
    constructor(formElement, containerElement, quizId) {
        this.form = formElement;
        this.container = containerElement;
        this.quizId = quizId;
    }

    init() {
        if (!this.quizId || !this.form || !this.container) {
            console.error("EditQuiz: Missing required elements or quizId.");
            return;
        }

        this.fetchQuizData();
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
    }

    escapeHtml(text) {
        if (typeof text !== "string") return text;
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    fetchQuizData() {
        fetch(`/api/quiz/details?id=${this.quizId}`)
            .then(res => res.json())
            .then(quiz => this.renderQuizForm(quiz))
            .catch(e => alert("Failed to load quiz: " + e));
    }

    renderQuizForm(quiz) {
        let html = `
            <label>Quiz title</label>
            <input name="title" value="${this.escapeHtml(quiz.title)}" required>
        `;

        quiz.questions.forEach((q, qi) => {
            html += this.renderQuestion(q, qi);
        });

        this.container.innerHTML = html;
    }

    renderQuestion(q, qi) {
        return `
            <fieldset>
                <legend>Question ${qi + 1}</legend>
                <input name="questions[${qi}][id]" type="hidden" value="${q.id}">
                <label>Question text</label>
                <input name="questions[${qi}][text]" value="${this.escapeHtml(q.text)}" required>
                ${this.renderAnswers(q.answers, qi)}
            </fieldset>
        `;
    }

    renderAnswers(answers, qi) {
        return answers.map((a, ai) => `
            <div class="answer-container">
                <input type="hidden" name="questions[${qi}][answers][${ai}][id]" value="${a.id}">
                <input name="questions[${qi}][answers][${ai}][text]" value="${this.escapeHtml(a.text)}" required>
                <label class="radio-container">
                    <input type="radio" name="questions[${qi}][correct]" value="${ai}" ${a.is_correct ? 'checked' : ''}>
                    correct
                </label>
            </div>
        `).join('');
    }

    async handleSubmit(e) {
        e.preventDefault();
        const formData = new FormData(this.form);
        formData.append('quizId', this.quizId);

        try {
            const res = await fetch(`/api/quiz/update`, {
                method: 'POST',
                body: formData
            });

            if (!res.ok) {
                const errorText = await res.text();
                throw new Error(errorText || 'Unknown error');
            }
            alert('Quiz updated');
        } catch (e) {
            alert('Update failed: ' + e.message);
        }
    }
}