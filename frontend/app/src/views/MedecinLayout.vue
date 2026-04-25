<script setup>
import { useRouter } from 'vue-router';
import AppButton from '@/components/ui/AppButton.vue';
import BaseIcon from '@/components/ui/BaseIcon.vue';

const router = useRouter();
const medecinData = JSON.parse(localStorage.getItem('user_info') || '{}');

const handleLogout = () => {
  localStorage.clear();
  router.push('/login');
};

const navItems = [
  { name: 'Dashboard', path: '/medecin', icon: 'barChart' },
  { name: 'Mes Patients', path: '/medecin/patients', icon: 'users' },
  { name: 'Consultations', path: '/medecin/consultations', icon: 'calendar' },
  { name: 'Réponses Patients', path: '/medecin/responses', icon: 'fileText' },
  { name: 'Questions FAQ', path: '/medecin/questions', icon: 'helpCircle' },
];
</script>

<template>
  <div class="flex h-screen bg-slate-50 overflow-hidden font-sans">
    <!-- Sidebar -->
    <aside class="hidden md:flex flex-col w-72 bg-slate-900 text-slate-300 transition-all duration-300">
      <div class="p-6 flex items-center gap-4 border-b border-slate-800">
        <div class="w-12 h-12 bg-primary/20 flex items-center justify-center rounded-2xl text-primary shadow-lg shadow-primary/10 border border-primary/20">
          <BaseIcon name="hospital" :size="24" stroke-width="2.5" />
        </div>
        <div>
          <h1 class="text-white font-black text-xl leading-none tracking-tight">E-Sante</h1>
          <p class="text-[10px] text-slate-500 font-bold tracking-[0.1em] uppercase mt-1">Medecin Panel</p>
        </div>
      </div>

      <nav class="flex-1 px-4 py-8 space-y-2 overflow-y-auto">
        <router-link
          v-for="item in navItems"
          :key="item.path"
          :to="item.path"
          class="flex items-center gap-3.5 px-4 py-3.5 rounded-2xl transition-all duration-300 group hover:text-white hover:bg-white/5"
          :class="{ 'bg-primary !text-white shadow-xl shadow-primary/25 translate-x-1': $route.path === item.path }"
        >
          <div class="flex items-center justify-center transition-transform group-hover:scale-110">
            <BaseIcon :name="item.icon" :size="20" />
          </div>
          <span class="font-bold text-sm tracking-tight">{{ item.name }}</span>
        </router-link>
      </nav>

      <div class="p-6 border-t border-slate-800 bg-slate-900/50">
        <AppButton
          variant="ghost"
          custom-class="w-full justify-start text-slate-400 hover:text-red-400 hover:bg-red-400/10 rounded-2xl group transition-all"
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
      <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-10 sticky top-0 z-40">
        <div class="flex items-center gap-3 text-sm font-bold">
          <span class="text-slate-400">Pages</span>
          <span class="text-slate-300">/</span>
          <span class="text-slate-900 tracking-tight">{{ $route.name || 'Dashboard' }}</span>
        </div>

        <div class="flex items-center gap-6">
          <div class="hidden sm:flex flex-col items-end">
            <span class="text-sm font-black text-slate-900 leading-none">Dr. {{ medecinData.nom }}</span>
            <span class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1">Médecin Spécialiste</span>
          </div>
          <div class="w-12 h-12 bg-primary text-white flex items-center justify-center rounded-2xl font-black shadow-lg shadow-primary/20 ring-4 ring-primary/5 transition-transform hover:scale-105">
            {{ medecinData.nom?.charAt(0) }}
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 overflow-y-auto p-10">
        <div class="max-w-7xl mx-auto">
          <router-view v-slot="{ Component }">
            <transition name="fade-page" mode="out-in">
              <component :is="Component" />
            </transition>
          </router-view>
        </div>
      </main>
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
