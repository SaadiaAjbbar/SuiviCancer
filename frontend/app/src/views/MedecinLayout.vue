  <script setup>
  import { useRouter } from 'vue-router';

  const router = useRouter();
  const medecinData = JSON.parse(localStorage.getItem('user_info') || '{}');

  const handleLogout = () => {
    localStorage.clear();
    router.push('/login');
  };
</script>

<template>
  <div class="layout-wrapper">
    <aside class="sidebar">
      <div class="brand-logo">
        <span class="icon">🏥</span>
        <div class="text">
          <h3>E-Sante</h3>
          <small>Medecin Panel</small>
        </div>
      </div>

      <nav class="sidebar-nav">
        <router-link to="/medecin" class="nav-item" active-class="active-link" exact>
          <span class="nav-icon">📊</span> Dashboard
        </router-link>
        <router-link to="/medecin/patients" class="nav-item" active-class="active-link">
          <span class="nav-icon">👥</span> Mes Patients
        </router-link>
        <router-link to="/medecin/consultations" class="nav-item" active-class="active-link">
          <span class="nav-icon">📅</span> Consultations
        </router-link>
        <router-link to="/medecin/responses" class="nav-item" active-class="active-link">
          <span class="nav-icon">📝</span> Réponses Patients
        </router-link>
        <router-link to="/medecin/questions" class="nav-item" active-class="active-link">
          <span class="nav-icon">❓</span> Questions FAQ
        </router-link>
      </nav>

      <div class="sidebar-footer">
        <button @click="handleLogout" class="btn-logout">
          <span>🚪</span> Déconnexion
        </button>
      </div>
    </aside>

    <div class="main-container">
      <header class="top-navbar">
        <div class="breadcrumb">
          <span>Pages / </span>
          <strong>{{ $route.name }}</strong>
        </div>
        <div class="user-meta">
          <span class="doctor-name">Dr. {{ medecinData.nom }}</span>
          <div class="avatar">{{ medecinData.nom?.charAt(0) }}</div>
        </div>
      </header>

      <main class="page-content">
        <router-view v-slot="{ Component }">
          <transition name="fade" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </main>
    </div>
  </div>
</template>

<style scoped>
.layout-wrapper {
  display: flex;
  height: 100vh;
  background-color: #f4f7fe;
  overflow: hidden;
}

/* Sidebar Styling */
.sidebar {
  width: 280px;
  background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
  color: white;
  display: flex;
  flex-direction: column;
  padding: 1.5rem;
  box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
}

.brand-logo {
  display: flex;
  align-items: center;
  gap: 12px;
  padding-bottom: 2rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.brand-logo .icon {
  font-size: 2rem;
}

.brand-logo h3 {
  margin: 0;
  font-size: 1.2rem;
  letter-spacing: 1px;
}

.brand-logo small {
  color: #94a3b8;
}

.sidebar-nav {
  margin-top: 2rem;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  color: #cbd5e1;
  text-decoration: none;
  border-radius: 12px;
  transition: all 0.3s ease;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.05);
  color: white;
}

.active-link {
  background: #3b82f6 !important;
  color: white !important;
  box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
}

.sidebar-footer {
  padding-top: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.btn-logout {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px;
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
  border: 1px solid rgba(239, 68, 68, 0.2);
  border-radius: 10px;
  cursor: pointer;
  font-weight: 600;
  transition: 0.3s;
}

.btn-logout:hover {
  background: #ef4444;
  color: white;
}

/* Main Content Area Styling */
.main-container {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  overflow-y: auto;
}

.top-navbar {
  height: 70px;
  background: rgba(244, 247, 254, 0.8);
  backdrop-filter: blur(10px);
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 2rem;
  position: sticky;
  top: 0;
  z-index: 10;
}

.doctor-name {
  font-weight: 600;
  color: #2d3748;
  margin-right: 12px;
}

.avatar {
  width: 40px;
  height: 40px;
  background: #3b82f6;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  font-weight: bold;
}

.page-content {
  padding: 2rem;
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
