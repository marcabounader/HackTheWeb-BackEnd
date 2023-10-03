<img src="./readme/title1.svg"/>

<br><br>

<!-- Table of contents -->
<img src="./readme/title.svg"/>

1. [Project Philosophy](#project-philosophy)
2. [Prototyping](#prototyping)
3. [Implementation](#implementation)
4. [Tech Stack](#teck-stack)
5. [Prototyping](#prototyping)
6. [Demo](#demo)
7. [OpenAI](#OpenAI)
8. [Performance](#performance)
9. [How to Run](#how-to-run)
   1. [Prerequisites](#prerequisites)
   2. [FrontEnd Installation](#frontend-installation)
   3. [BackEnd Installation](#backend-installation)
   4. [Repository Links](#repo-links)
   
<br><br>

<!-- project philosophy -->
<a name="project-philosophy"></a>
<img src="./readme/title2.svg"/>

> The HackTheWeb platform serves as an educational tool, providing users with a comprehensive understanding of various web application attacks and their practical execution.
> HackTheWeb's aim is to empower users with the knowledge and skills required to either build robust, secure web applications or to effectively assess their security through penetration testing. Participants will advance through the program by progressively mastering web application attacks, advancing to higher levels upon successful completion. Additionally, an AI chatbot is available to assist users when they encounter challenges at any level, offering valuable information and tips to help them overcome obstacles and progress further.

### User Stories

#### Students
- As a student, i want to login to the platform so that i would see the user dashboard.
- As a student, i want to be able to list all the labs so that i would press the one i am interested in.
- As a student, i want to be able to restart the lab so that i would be able to implement the attack again

#### Web App Developer
- As a web app developer, i want to be able to see the ranks of the top hackers so that i would evaluate myself.
- As a web app developer, i want to view the implementation so that i would be able to securely develop my apps.
- As a web app developer, i want to be able to alter between levels so that i would implement more advanced attacks. 

#### Penetration Tester
- As a penetration tester, i want to implement web app attacks on the platform so that i would know how to test it on my company’s application.
- As a penetration tester, i want to be able to have a score that i would rank between other professionals.

#### SOC Analyst
- As an SOC analyst, i want to communicate with an AI-driven chatbot so that i would be guided on how to implement the attack.
- As a SOC analyst, i want to logout from the platform so that i would stop training.

#### DevSecOps Professional
- As a DevSecOps professional, i want to be able to see the vulnerable code so that i would be able to secure my projects.
- As a DevSecOps professional, i want to see details about the attack definition so that i would learn more about web app attacks before implementing.

#### Admin
- As an admin, i want to be able to login to the platform so that i would access to admin console.
- As an admin, i want to be able to perform CRUD operations so that i would manage users.
- As an admin, i want to be able to perform CRUD operations so that i would manage labs
- As an admin, i want to be able to perform CRUD operations so that i would manage badges.

<br><br>

<!-- Prototyping -->
<a name="prototyping"></a>
<img src="./readme/title3.svg"/>

> We designed HackTheWeb using wireframes and mockups, iterating on the design until we reached the ideal layout for easy navigation and a seamless user experience.

### Mockups

#### Hacker
| Landing screen  | Home Screen | Badges Screen |
| ---| ---| ---|
| ![Landing](./readme/Mockups/Hacker/Index.png) | ![Home](./readme/Mockups/Hacker/Home.png) | ![Badges](./readme/Mockups/Hacker/Achievements.png) |
| Labs screen  | Active Screen | Lab Modal Screen |
| ![Active Labs](./readme/Mockups/Hacker/Home.png) | ![Active Labs](./readme/Mockups/Hacker/Active%20Labs.png) | ![Leaderboard](./readme/Mockups/Hacker/Leaderboard.png) |

#### Admin
| Home screen  | Labs Screen | Active Labs Screen |
| ---| ---| ---|
| ![Home](./readme/Mockups/Admin/Home.png) | ![Labs](./readme/Mockups/Admin/Labs.png) | ![Active Labs](./readme/Mockups/Admin/Active%20Labs.png) |
| Badges screen  | Users Screen | Add Lab Screen |
| ![Badges](./readme/Mockups/Admin/Badges.png) | ![Users](./readme/Mockups/Admin/Users.png) | ![Add Lab](./readme/Mockups/Admin/Add%20Lab.png) |
<br><br>

<!-- Implementation -->
<a name="implementation"></a>
<img src="./readme/title4.svg"/>

> Using the wireframes and mockups as a guide, we implemented the HackTheWeb app with the following features:

### Admin Screens (Web)
| Home screen  | Badges screen |  Labs screen |
| ---| ---| ---|
| ![Home](./readme/AdminScreens/home.png) | ![Badges](./readme/AdminScreens/badges.png) | ![Labs](./readme/AdminScreens/labs.png) |
| Active Labs screen  | Users Screen | Add Lab Screen |
| ![Active Labs](./readme/AdminScreens/active-labs.png) | ![Users](./readme/AdminScreens/users.png) | ![Add Lab](./readme/AdminScreens/add-lab.png) |

<br><br>

<!-- Tech stack -->
<a name="teck-stack"></a>
<img src="./readme/title5.svg"/>

###  HackTheWeb is built using the following technologies:

- This project uses the [React JS](https://legacy.reactjs.org/) library. ReactJS (aka React) is an open-source JavaScript-based user interface library. It is insanely popular in web development communities today. React Native is equally popular among mobile application developers.
- For persistent storage (database), the app uses [MySQL](https://www.mysql.com/), an open-source relational database management system (RDBMS) that is widely used for storing and managing structured data.
- This project uses the [Laravel](https://laravel.com/) PHP Framework. Laravel is a powerful and popular open-source PHP framework known for its simplicity and elegance. It simplifies web application development by providing tools and libraries for tasks like routing, authentication, and database management, enabling developers to build robust and maintainable web applications more efficiently.
- To create isolated labs, the app uses the [Docker](https://www.docker.com/) technology. Docker is a platform that enables developers to create, deploy, and run applications in containers.
- The app uses the font ["Roboto Mono"](https://fonts.google.com/specimen/Roboto+Mono) as its main font, and the design of the app adheres to the material design guidelines.

<br><br>

<!-- Demo -->
<a name="demo"></a>
<img src="./readme/title6.svg"/>

### Hacker
| Home screen  | Badges screen |
| ---| ---|
| ![Home](./readme/GIFs/Hacker/user-1.gif) | ![Badges](./readme/GIFs/Hacker/user-2.gif) |
| Lab1 screen  | Lab2 screen |
| ![Labs1](./readme/GIFs/Hacker/user-3.gif) | ![Labs1](./readme/GIFs/Hacker/user-4.gif) |

### Admin
| Landing screen  | Home screen |
| ---| ---|
| ![Landing](./readme/GIFs/Admin/admin-1.gif) | ![Home](./readme/GIFs/Admin/admin-2.gif) |
| Badges screen  | Labs screen |
| ![Badges](./readme/GIFs/Admin/admin-3.gif) | ![Labs](./readme/GIFs/Admin/admin-4.gif) |

### Youtube
* [Hacker](https://youtu.be/VaY9ahxzs2I)
* [Admin](https://youtu.be/aXHIg-stIlk)

<br><br>

<!--Open AI-->
<a name="OpenAI"></a>
<img src="./readme/title7.svg"/>

### ChatBot Prompt Engineering
```sh
   let initialMessage = "This message is being sent using HackTheWeb Assistant to aid the user on how to implement web app attacks.";
   initialMessage += "\nThe user will be asking questions related to the app.";
   initialMessage += "\nThe app contains different labs for implementing different web attacks according to OWASP standards.";
   initialMessage += "\nThe app is using Docker to run instances of OWASP Mutillidae 2.";
   initialMessage += "\nEach instance is a different attack scenario.";
   initialMessage += "\nAnswer the question as if you are HackTheWeb Assistant.";
   initialMessage += "\nThe user is asking the following questions:";
```
<br><br>

<!--Performance-->
<a name="performance"></a>
<img src="./readme/title8.svg"/>

* [Postman Admin Collection](./readme/Postman/HackTheWeb-Admin.postman_collection.json)
* [Postman User Collection](./readme/Postman/HackTheWeb-User.postman_collection.json)

<br><br>

<!-- How to run -->
<a name="how-to-run"></a>
<img src="./readme/title9.svg"/>

> To set up HackTheWeb locally, follow these steps:

<a name="teck-stack"></a>

### Prerequisites
<a name="prerequisites"></a>

* Install Node Js.
* Install PHP.
* Install Composer.
* Install Docker.
* Install XAMPP.

### FrontEnd Installation
<a name="frontend-installation"></a>
1. Clone the repo
   ```sh
   git clone https://github.com/marcabounader/HackTheWeb-FrontEnd.git
   ```
2. Change base url of helper function according to backend url(\src\helpers):
3. Install NPM packages
   ```sh
   npm install
   ```
4. Change the following base URLs in auth.helpers.js: base_url, base_url2.
5. Run the APP:
   ```sh
   npm run
   ```

### BackEnd Installation
<a name="backend-installation"></a>
1. Clone the repo
   ```sh
   git clone https://github.com/marcabounader/HackTheWeb-BackEnd.git
   ```
2. Install NPM packages
   ```sh
   compose install
   ```
4. Run XAMPP and create database named "hackthebox_db"
5. Build the LAB images using docker-compose. 
6. Create .env file in root folder of project and change the following variables: DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD, CHATGTP_API_KEY, APP_URL,APP_IP.
7. Link the storage:
   ```sh
   php artisan storage:link
   ```
8. Migrate the tables with seeding:
   ```sh
   php artisan migrate --seed
   ```
10. Seed the tables with other seeders:
   ```sh
   php artisan db:seed --class=UserSeeder
   ```
   ```sh
   php artisan db:seed --class=LabSeeder
   ```
   ```sh
   php artisan db:seed --class=BadgeSeeder
   ```
11. Generate JWT secret:
   ```sh
   php artisan jwt:secret
   ```
12. Run the APP:
   ```sh
   php artisan serve
   ```

### Repository Links
<a name="repo-links"></a>
- [FrontEnd](https://github.com/marcabounader/HackTheWeb-FrontEnd.git)
- [BackEnd](https://github.com/marcabounader/HackTheWeb-BackEnd.git)
- [JWT Lab](https://github.com/marcabounader/mutillidae-jwt.git)
- [Command Injection Lab](https://github.com/marcabounader/mutillidae-ci.git)
- [SQLi Lab](https://github.com/marcabounader/mutillidae-sqli.git)

Now, you should be able to run HackTheWeb locally and explore its features.
