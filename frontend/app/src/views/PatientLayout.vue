<script setup>
import { useRouter } from 'vue-router';
import AppButton from '@/components/ui/AppButton.vue';
import BaseIcon from '@/components/ui/BaseIcon.vue';

const router = useRouter();
const userData = JSON.parse(localStorage.getItem('user_data') || '{}');
const token = localStorage.getItem('user_token');

const handleLogout = async () => {
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

  localStorage.removeItem('user_token');
  localStorage.removeItem('user_data');
  localStorage.removeItem('user_role');
  router.push('/login');
};

const navItems = [
  { name: 'Dashboard', path: '/patient', icon: 'home' },
  { name: 'Mes Bilans', path: '/patient/bilans', icon: 'stetho' },
  { name: 'Rendez-vous', path: '/patient/rendez-vous', icon: 'calendar' },
  { name: 'Traitements', path: '/patient/traitements', icon: 'pill' },
  { name: 'Conseils', path: '/patient/conseils', icon: 'lightbulb' },
  { name: 'Consultations', path: '/patient/consultations', icon: 'hospital' },
];
</script>

<template>
  <div class="flex h-screen bg-slate-50 overflow-hidden font-sans">
    <!-- Sidebar -->
    <aside class="hidden lg:flex flex-col w-72 bg-white border-r border-slate-200 transition-all duration-300">
      <div class="p-6 flex items-center gap-4 border-b border-slate-100">
        <div class="w-12 h-12 bg-primary/10 flex items-center justify-center rounded-2xl text-primary shadow-inner">
          <BaseIcon name="hospital" :size="24" stroke-width="2.5" />
        </div>
        <div>
          <h1 class="text-slate-900 font-black text-xl leading-none tracking-tight">E-Sante</h1>
          <p class="text-[10px] text-slate-400 font-bold tracking-[0.1em] uppercase mt-1">Espace Patient</p>
        </div>
      </div>

      <nav class="flex-1 px-4 py-8 space-y-2 overflow-y-auto">
        <router-link
          v-for="item in navItems"
          :key="item.path"
          :to="item.path"
          class="flex items-center gap-3.5 px-4 py-3.5 rounded-2xl transition-all duration-300 group"
          :class="{ 'bg-primary text-white shadow-xl shadow-primary/25 translate-x-1': $route.path === item.path }"
        >
          <div class="flex items-center justify-center transition-transform group-hover:scale-110">
            <BaseIcon :name="item.icon" :size="20" />
          </div>
          <span class="font-bold text-sm tracking-tight">{{ item.name }}</span>
        </router-link>
      </nav>

      <div class="p-6 border-t border-slate-100 bg-slate-50/50">
        <AppButton
          variant="ghost"
          custom-class="w-full justify-start text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-2xl group transition-all"
          @click="handleLogout"
        >
          <BaseIcon name="logout" :size="18" class="mr-3 group-hover:translate-x-1 transition-transform" />
          <span class="font-bold text-sm">Déconnexion</span>
        </AppButton>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <!-- Top Navbar -->
      <header class="h-20 bg-white/80 backdrop-blur-xl border-b border-slate-200 flex items-center justify-between px-8 sticky top-0 z-40">
        <div class="lg:hidden flex items-center gap-3">
           <div class="w-10 h-10 bg-primary flex items-center justify-center rounded-xl text-white shadow-lg shadow-primary/20">
             <BaseIcon name="hospital" :size="20" />
           </div>
           <span class="font-black text-slate-900 tracking-tight">E-Sante</span>
        </div>
        
        <div class="hidden lg:flex items-center gap-3 text-sm font-bold">
          <span class="text-slate-400">Dashboard</span>
          <BaseIcon name="home" :size="14" class="text-slate-300" />
          <span class="text-slate-900 tracking-tight">{{ $route.name || 'Aperçu' }}</span>
        </div>

        <div class="flex items-center gap-4">
          <div class="flex flex-col items-end">
            <span class="text-sm font-black text-slate-900 leading-none">{{ userData.nom }} {{ userData.prenom }}</span>
            <span class="text-[10px] text-primary font-bold uppercase tracking-widest mt-1">Patient Vérifié</span>
          </div>
          <div class="w-11 h-11 bg-gradient-to-br from-slate-100 to-slate-200 text-slate-700 flex items-center justify-center rounded-2xl font-black border-2 border-white shadow-sm ring-1 ring-slate-200">
            {{ userData.nom?.charAt(0) }}{{ userData.prenom?.charAt(0) }}
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 overflow-y-auto p-6 lg:p-10">
        <div class="max-w-6xl mx-auto">
          <router-view v-slot="{ Component }">
            <transition name="fade-page" mode="out-in">
              <component :is="Component" />
            </transition>
          </router-view>
        </div>
      </main>

      <!-- Mobile Navigation -->
      <nav class="lg:hidden flex items-center justify-around bg-white/90 backdrop-blur-lg border-t border-slate-200 h-20 px-4 pb-2">
        <router-link
          v-for="item in navItems.slice(0, 5)"
          :key="item.path"
          :to="item.path"
          class="flex flex-col items-center justify-center flex-1 py-2 rounded-2xl transition-all"
          :class="{ 'text-primary bg-primary/5 font-black': $route.path === item.path }"
        >
          <BaseIcon :name="item.icon" :size="22" />
          <span class="text-[9px] uppercase tracking-widest font-black mt-1.5">{{ item.name.split(' ')[1] || item.name }}</span>
        </router-link>
      </nav>
    </div>
  </div>
</template>

<style>
.fade-page-enter-active,
.fade-page-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.fade-page-enter-from {
  opacity: 0;
  transform: translateY(10px);
}

.fade-page-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
