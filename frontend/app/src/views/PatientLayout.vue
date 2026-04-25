<template>
  <div class="patient-app">
    <nav class="patient-nav">
      <div class="nav-container">
        <div class="brand">
          <span class="icon">🏥</span>
          <span class="logo-text">E-Sante Patient</span>
        </div>

        <div class="user-menu">
          <div class="user-info">
            <span class="user-name">{{ userData.nom }} {{ userData.prenom }}</span>
            <span class="user-role">Patient</span>
          </div>

        </div>
      </div>
    </nav>

    <div class="layout-body">
      <aside class="sidebar">
        <router-link to="/patient" class="nav-item" exact-active-class="active">
          <span class="nav-icon">🏠</span>
          <span class="nav-label">Tableau de bord</span>
        </router-link>

        <router-link to="/patient/bilans" class="nav-item" active-class="active">
          <span class="nav-icon">🩺</span>
          <span class="nav-label">Mes Bilans</span>
        </router-link>

        <router-link to="/patient/rendez-vous" class="nav-item" active-class="active">
          <span class="nav-icon">📅</span>
          <span class="nav-label">Mes Rendez-vous</span>
        </router-link>

        <router-link to="/patient/traitements" class="nav-item" active-class="active">
          <span class="nav-icon">💊</span>
          <span class="nav-label">Mes Traitements</span>
        </router-link>

        <router-link to="/patient/conseils" class="nav-item" active-class="active">
          <span class="nav-icon">💡</span>
          <span class="nav-label">Mes Conseils</span>
        </router-link>

        <router-link to="/patient/consultations" class="nav-item" active-class="active">
          <span class="nav-icon">🏥</span>
          <span class="nav-label">Mes Consultations</span>
        </router-link>
        <button @click="handleLogout" class="btn-logout" title="Déconnexion">
          <span class="logout-icon">🚪</span>
          <span class="logout-text">Déconnexion</span>
        </button>

      </aside>

      <main class="main-content">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';

const router = useRouter();
// On récupère les données de l'utilisateur pour l'affichage
const userData = JSON.parse(localStorage.getItem('user_data') || '{}');
const token = localStorage.getItem('user_token');

const handleLogout = async () => {
  // Optionnel : Avertir le backend pour révoquer le token
  try {
    await fetch('http://localhost:8080/api/logout', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
  } catch (error) {
    console.error("Erreur lors de la déconnexion backend:", error);
  }

  // 1. Vider le stockage local
  localStorage.removeItem('user_token');
  localStorage.removeItem('user_data');
  localStorage.removeItem('user_role');

  // Alternative radicale : localStorage.clear();

  // 2. Rediriger vers la page de login
  router.push('/login');
};

</script>

<style scoped>
/* Reset & Layout */
.patient-app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background-color: #f4f7f6;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

/* Navbar */
.patient-nav {
  background-color: #ffffff;
  height: 70px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  display: flex;
  align-items: center;
  position: sticky;
  top: 0;
  z-index: 1000;
}

.nav-container {
  width: 100%;
  padding: 0 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.brand {
  display: flex;
  align-items: center;
  gap: 12px;
}

.logo-text {
  font-size: 1.3rem;
  font-weight: 800;
  color: #2c3e50;
  letter-spacing: -0.5px;
}

.user-menu {
  display: flex;
  align-items: center;
  gap: 20px;
}

.user-info {
  text-align: right;
  line-height: 1.2;
}

.user-name {
  display: block;
  font-weight: 600;
  color: #2c3e50;
}

.user-role {
  font-size: 0.75rem;
  color: #3498db;
  font-weight: bold;
  text-transform: uppercase;
}

.btn-logout {
  background: #fff5f5;
  border: 1px solid #feb2b2;
  font-size: 1.2rem;
  padding: 6px 12px;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-logout:hover {
  background: #fed7d7;
  transform: translateY(-1px);
}

/* Body & Sidebar */
.layout-body {
  display: flex;
  flex: 1;
}

.sidebar {
  width: 280px;
  background-color: #ffffff;
  border-right: 1px solid #eef2f7;
  padding: 25px 15px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 14px 18px;
  text-decoration: none;
  color: #64748b;
  border-radius: 12px;
  font-weight: 500;
  transition: all 0.2s;
}

.nav-item:hover {
  background-color: #f8fafc;
  color: #334155;
}

.nav-item.active {
  background-color: #3498db;
  color: #ffffff;
  box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
}

.nav-icon {
  font-size: 1.2rem;
}

/* Content Area */
.main-content {
  flex: 1;
  padding: 2rem;
  overflow-y: auto;
}

/* Responsive (Tablettes et Mobiles) */
@media (max-width: 992px) {
  .sidebar {
    width: 80px;
  }

  .nav-label {
    display: none;
  }

  .nav-item {
    justify-content: center;
    padding: 15px;
  }
}

.btn-logout {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #fff0f0;
  border: 1px solid #ffcccc;
  color: #e53e3e;
  padding: 8px 15px;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s ease;
}

.btn-logout:hover {
  background: #ffe0e0;
  transform: translateY(-1px);
  box-shadow: 0 2px 5px rgba(229, 62, 62, 0.1);
}

.logout-icon {
  font-size: 1.2rem;
}



@media (max-width: 768px) {
  .logout-text {
    display: none;
  }

  .sidebar {
    position: fixed;
    bottom: 0;
    width: 100%;
    height: 70px;
    flex-direction: row;
    justify-content: space-around;
    padding: 0;
    border-right: none;
    border-top: 1px solid #eef2f7;
    z-index: 1000;
  }

  .main-content {
    padding: 1rem;
    padding-bottom: 80px;
    /* Espace pour la barre mobile */
  }
}
</style>
