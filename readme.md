# Tytuł aplikacji
Tune In Quiz <br/>
Edukacja

## Opis Aplikacji

Aplikacja quizowa do ćwiczenia rozpoznawania utworów muzycznych odtwarzanych z plików MP3. Użytkownik odpowiada na pytania typu „co to za utwór” lub „kto zagrał solówkę”. Zawiera logowanie, ekran główny z wyborem gry lub tworzeniem quizu, odtwarzanie i ocenę wyników, a także panel admina do zarządzania użytkownikami.

# Dokumentacja

## Diagram ERD

![Alt text](docs/TuneInQuizDB.drawio.png)

https://drive.google.com/file/d/1L9fes6S468Pz1fw1LcLS8Wl7gbF4a2hT/view?usp=sharing

## Screeny Aplikacji

Register
![Alt text](docs/registerView.png "Register")

Login
![Alt text](docs/loginView.png "Login")

Home
![Alt text](docs/homeView.png "Home")

Create Quiz
![Alt text](docs/createQuizView.png "Create Quiz")

Add Question
![Alt text](docs/addQuestionView.png "Add Question to quiz")

Play Quiz
![Alt text](docs/quizView.png "Play Quiz")

Quiz Result
![Alt text](docs/resultView.png "Quiz Result")

Admin Page
![Alt text](docs/adminView.png "Admin page")

## Diagram architektury aplikacji

![Alt text](docs/architecture.drawio.png "Diagram architektury aplikacji")

## Instrukcja uruchomienia

Uruchomienie kontenera: <br/>
<code>docker compose up</code> <br/>
Uruchomienie testów: <br/>
<code>docker-compose run --rm php phpunit --testdox tests</code> <br/>
<code>docker-compose run --rm php sh tests/integration/register_test.sh</code> <br/>

Zmienne środowiskowe: .env
<code>
```env
DB_HOST=db
DB_NAME=db
DB_USER=docker
DB_PASS=docker</code>

