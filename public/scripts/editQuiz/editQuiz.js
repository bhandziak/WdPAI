const quizId = new URLSearchParams(window.location.search).get('id');

fetch(`/api/quiz/details?id=${quizId}`)
    .then(res => res.json())
    .then(quiz => renderQuizForm(quiz))
    .catch((e) => alert("Failed to load quiz" + e));

function escapeHtml(text) {
    if (typeof text !== "string") return text;

    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}


function renderQuizForm(quiz) {
    const form = document.getElementById('quiz-form-body');

    form.innerHTML = `
        <label>Quiz title</label>
        <input name="title" value="${escapeHtml(quiz.title)}" required>
    `;

    quiz.questions.forEach((q, qi) => {
        form.innerHTML += renderQuestion(q, qi);
    });
}

function renderQuestion(q, qi) {
    return `
        <fieldset>
            <legend>Question ${qi + 1}</legend>

            <input
                name="questions[${qi}][id]"
                type="hidden"
                value="${q.id}"
            >

            <label>Question text</label>
            <input
                name="questions[${qi}][text]"
                value="${escapeHtml(q.text)}"
                required
            >

            ${renderAnswers(q.answers, qi)}
        </fieldset>
    `;
}

function renderAnswers(answers, qi) {
    return answers.map((a, ai) => `
        <div class="answer-container">
            <input type="hidden"
                name="questions[${qi}][answers][${ai}][id]"
                value="${a.id}"
            >

            <input
                name="questions[${qi}][answers][${ai}][text]"
                value="${escapeHtml(a.text)}"
                required
            >

            <label class="radio-container">
                <input
                    type="radio"
                    name="questions[${qi}][correct]"
                    value="${ai}"
                    ${a.is_correct ? 'checked' : ''}
                >
                correct
            </label>
        </div>
    `).join('');
}

document
    .querySelector('.quiz-form')
    .addEventListener('submit', e => {
        e.preventDefault();

        const formData = new FormData(e.target);

        formData.append('quizId', quizId);

        fetch(`/api/quiz/update`, {
            method: 'POST',
            body: formData
        })
            .then(res => {
                if (!res.ok) throw new Error();
                alert('Quiz updated');
            })
            .catch(() => alert('Update failed'));
    });
