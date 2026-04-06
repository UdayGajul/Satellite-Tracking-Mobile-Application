
# 🛰️ Satellite Tracking Mobile Application

> A mobile app that tracks satellites in real time and displays their position on a 3D Earth model.  
> Provides users with information such as current coordinates and other satellite details.

**_⭐ This project was completed as my Master’s final year research project with my classmates in a team effort. I selected the topic of satellite tracking, performed research and a literature review, identified a real‑world problem, worked on implementing the Three.js 3D Earth model, handling complex TLE data, integrated API and React Native components. The final result is this mobile application, developed as a practical solution to the identified problem._**

## 📌 Features

### 👤 User Features

1. 👤 User registration and login so only authenticated users can use the app  
2. 📘 After login, users see a brief “How to Operate the App” instruction page  
3. 🧭 Simple 3D Earth model visible when the app launches  
4. 💫 Users can choose orbit categories (LEO, MEO, GEO) using the app’s navigation drawer to view satellites in the selected orbit  
5. 🌐 Only top satellites from selected countries (India, Russia, China, USA, UK) are available  
6. 🛰️ Live tracking where satellites move on the Earth model in real time  
7. 📊 Basic satellite information such as coordinates, altitude and other details  
8. ❓ FAQ section on satellite  
9. ⭐ In‑app rating system allowing users to rate the application and submit feedback

### 🔐 Admin Features

10. 🖥️ Admin dashboard shows total satellites, active/inactive counts, total users and more  
11. 🛠️ Admin can add new satellites, which are automatically reflected in the application  
12. 🔄 Real‑time data updates ensure newly added or modified satellites are instantly visible to users  
13. 🚫 Admin can disable satellites that are inactive or out of service  
14. 📝 Admin can review user feedback and ratings to make informed decisions for future improvements

## 📁 Project Details & Demo

👉 Link to Google Drive folder containing the full project source code (same as uploaded on GitHub) and the app screen recording (.zip file):  
[Click here](https://drive.google.com/drive/folders/1vSjPoFO_nDfXWpemNirdfeVyEP-2pmP3?usp=sharing)

## 🛠️ Tech Stack

**Frontend:** React Native for building the mobile app using JavaScript and native UI components.  
**Backend & Database:** PHP running on XAMPP (Apache + MySQL) to handle server logic and database interactions.  
**API Testing & Development:** Postman for creating, testing, and debugging the app’s API endpoints.  
**3D Earth Visualization:** Three.js for rendering the interactive 3D Earth model and satellite paths.  
**Satellite Orbit Data:** Uses TLE data from CelesTrak to calculate and show satellite positions.
