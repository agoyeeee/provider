<script setup>
import { Moon, Sun } from "lucide-vue-next";
import { ref, onMounted } from "vue";

const theme = ref("light");

const toggleTheme = () => {
  theme.value = theme.value === "light" ? "dark" : "light";
  document.documentElement.classList.toggle("dark");
  localStorage.setItem("theme", theme.value);
};

onMounted(() => {
  const savedTheme = localStorage.getItem("theme") || "light";
  theme.value = savedTheme;
  if (savedTheme === "dark") {
    document.documentElement.classList.add("dark");
  }
});
</script>

<template>
  <button
    @click="toggleTheme"
    class="flex w-full items-center gap-2 rounded-md p-2 text-left text-sm outline-none ring-sidebar-ring transition-colors hover:bg-sidebar-accent hover:text-sidebar-accent-foreground focus-visible:ring-2 group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:p-2"
  >
    <Sun v-if="theme === 'light'" class="h-4 w-4 shrink-0" />
    <Moon v-else class="h-4 w-4 shrink-0" />
    <span class="group-data-[collapsible=icon]:hidden">
      {{ theme === 'light' ? 'Mode Terang' : 'Mode Gelap' }}
    </span>
  </button>
</template>
