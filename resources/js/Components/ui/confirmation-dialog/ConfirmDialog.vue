<script setup>
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/Components/ui/alert-dialog";

import { confirmDialogState } from "./index";
</script>

<template>
  <AlertDialog
    :open="confirmDialogState.isOpen.value"
    @update:open="confirmDialogState.isOpen.value = $event"
  >
    <AlertDialogContent class="bg-card">
      <AlertDialogHeader>
        <AlertDialogTitle>{{ confirmDialogState.title }}</AlertDialogTitle>
        <AlertDialogDescription>
          {{ confirmDialogState.description }}
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel class="cursor-pointer" @click="confirmDialogState.onCancel"
          >Batal</AlertDialogCancel
        >
        <AlertDialogAction
          class="button-primary text-white px-6"
          @click="confirmDialogState.onConfirm"
          >Ya</AlertDialogAction
        >
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
