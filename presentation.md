# E-Sante: Digital Healthcare Platform Presentation

## 🚀 Overview
E-Sante is a premium healthcare management platform designed to streamline hospital workflows and improve patient-doctor communication. The application is built with a modern Vue 3 frontend and a robust Laravel backend, featuring a state-of-the-art UI with a focus on usability and professional aesthetics.

---

## 🎨 Design System & UI Components
We have implemented a consistent, high-fidelity design system across the entire application:
- **BaseIcon**: A custom SVG icon system replacing generic emojis for a professional look.
- **AppButton & AppInput**: Reusable, styled components ensuring a unified form experience.
- **AppCard**: A premium container component used for dashboards and lists.
- **Glassmorphism & Gradients**: Used in navbars and headers for a modern, high-end feel.

---

## 👥 User Roles & Views Documentation

### 1. Authentication
*   **Login.vue**: A sleek entry point with secure token-based authentication.

### 2. Super Admin (Global Management)
*   **CreerHopital.vue**: Specialized view for creating and managing hospitals and their primary administrators.

### 3. Hospital Administrator
*   **AdminHopitalDashboard.vue**: Central hub for hospital management.
    *   **Manage Doctors & Nurses**: Full CRUD operations for medical staff.
    *   **Manage Master Data**: Configuration of toxicités and symptoms.
    *   **Tabbed Interface**: Organized view for efficient administration.

### 4. Medical Doctor (Expert Panel)
*   **MedecinLayout.vue**: The architectural container for all doctor-specific features, featuring a refined sidebar.
*   **DashboardView.vue**: At-a-glance overview of daily stats (consultations, active patients, pending questions).
*   **MesPatients.vue**: A detailed table of all patients assigned to the doctor, including contact info and profiles.
*   **ConsultationsView.vue**: The core of the medical workflow.
    *   **New Consultation**: Modernized form with multi-select for toxicités/symptoms.
    *   **Clinical Assessment**: Detailed bilan general with decisions for RDV, treatments, and advice.
*   **PatientResponses.vue**: Review and analysis of patient-submitted questionnaires.
*   **QuestionsView.vue**: Management of FAQ and medical questions for patient guidance.

### 5. Nurse (Patient Care)
*   **DashboardInfirmier.vue**: Focused on patient admission and triage.
    *   **Admission Form**: Streamlined patient registration and doctor assignment.
    *   **Active List**: Real-time view of hospitalized patients.

### 6. Patient (Self-Service Portal)
*   **PatientLayout.vue**: User-friendly layout with a bottom-nav for mobile accessibility and a clean sidebar for desktop.
*   **DashboardPatient.vue**: Personalized health overview and quick links.
*   **PatientBilans.vue**: Access to medical assessments and general health status updates.
*   **PatientConsultations.vue**: History of all medical visits.
*   **PatientRDV.vue**: Management and tracking of upcoming medical appointments.
*   **PatientTraitements.vue**: Detailed view of prescribed medications and dosages.
*   **PatientConseils.vue**: Repository of personalized medical advice from doctors.

---

## 🛠️ Recent Improvements
- **Navbar Refactor**: Complete overhaul of the navigation system with clean SVG icons, improved spacing, and responsive behaviors.
- **Form UX**: Modernized multi-select inputs for medical data entry, replacing clunky standard selects with interactive tags.
- **Active State Fixes**: Resolved navigation logic issues where multiple links appeared selected simultaneously.
- **Data Synchronization**: Fixed API endpoint inconsistencies to ensure real-time data accuracy across all views.

---

## 📈 Technical Stack
- **Frontend**: Vue 3 (Composition API), Vite, Tailwind CSS.
- **Icons**: Custom SVG-based BaseIcon component.
- **Routing**: Vue Router with role-based guard logic.
- **State Management**: Pinia / Local Storage.
- **Backend**: Laravel API.
