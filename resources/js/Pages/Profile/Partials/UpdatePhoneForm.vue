<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    
});

const user = usePage().props.auth.user;

const form = useForm({
    phone: user.phone,
   
});

const updatePhone = () => {
    form.patch('phone', {
        preserveScroll: true,
        onSuccess: () => form.reset,
        onError: () => {
            //
        }
    })
}
</script>

<template>
    <section>
        

        <form
            @submit.prevent="updatePhone"
            class="mt-6 space-y-6"
        >
            <div>
                <InputLabel for="phone" value="Phone number" />

                <TextInput
                    id="phone"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.phone"
                    autofocus
                
                />

                <InputError class="mt-2" :message="form.errors.phone" />
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Update Phone number</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600"
                    >
                       <span class="text-white bg-green-400 p-1 rounded-full"> Updated.</span>
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
