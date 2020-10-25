<template>
    <app-layout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-5 sm:p-6">
                    <jet-form-section @submitted="updateProfileInformation" class="pb-6">
                        <template #title>
                            Get Notified
                        </template>

                        <template #description>
                            Fill out the required fields to subscribe to notifications.
                        </template>

                        <template #form>
                            <!-- Name -->
                            <div class="col-span-6 sm:col-span-4">
                                <jet-label for="name" value="Name" />
                                <jet-input id="name" type="text" class="mt-1 block w-full" v-model="form.name" autocomplete="name" />
                                <jet-input-error :message="form.error('name')" class="mt-2" />
                            </div>

                            <!-- Email -->
                            <div class="col-span-6 sm:col-span-4">
                                <jet-label for="email" value="Email" />
                                <jet-input id="email" type="email" class="mt-1 block w-full" v-model="form.email" />
                                <jet-input-error :message="form.error('email')" class="mt-2" />
                            </div>

                            <!-- Phone -->
                            <div class="col-span-6 sm:col-span-4">
                                <jet-label for="phone" value="Phone" />
                                <jet-input id="phone" type="tel" class="mt-1 block w-full" v-model="form.phone" />
                                <jet-input-error :message="form.error('phone')" class="mt-2" />
                            </div>

                            <!-- Notification Preferences -->

                            <!-- I'd like to receive notifications for food drives -->
                            <div class="col-span-6 sm:col-span-4">
                                <div class="relative flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="notify_food_drive" type="checkbox" class="mt-1 block form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out" v-model="form.notify_food_drive" />
                                    </div>
                                    <div class="ml-3 text-sm leading-5">
                                        <jet-label for="notify_food_drive" class="font-medium text-gray-700" value="Food Drive Events" />
                                        <p class="text-gray-500">I'd like to be contacted to about food drives in my chosen area</p>
                                    </div>
                                    <jet-input-error :message="form.error('notify_food_drive')" class="mt-2" />
                                </div>

                            </div>


                            <template v-if="form.notify_food_drive">
                                <div class="col-span-6">
                                    <label for="street_address" class="block text-sm font-medium leading-5 text-gray-700">Street address</label>
                                    <jet-input id="street_address" class="mt-1 block w-full" v-model="form.street_address" />
                                </div>

                                <div class="col-span-6 sm:col-span-6 lg:col-span-2">
                                    <label for="city" class="block text-sm font-medium leading-5 text-gray-700">City</label>
                                    <jet-input id="city" class="mt-1 block w-full" v-model="form.city" />
                                </div>

                                <div class="col-span-6 sm:col-span-3 lg:col-span-2">
                                    <label for="state" class="block text-sm font-medium leading-5 text-gray-700">State / Province</label>
                                    <jet-input id="state" value="FL (only)" disabled readonly class="mt-1 block w-full" />
                                </div>

                                <div class="col-span-6 sm:col-span-3 lg:col-span-2">
                                    <label for="postal_code" class="block text-sm font-medium leading-5 text-gray-700">ZIP / Postal</label>
                                    <jet-input id="postal_code" class="mt-1 block w-full" v-model="form.postal_code" />
                                </div>

                                <div class="col-span-6 sm:col-span-4" v-if="form.notify_food_drive">
                                    <jet-label for="travel_distance" value="How far are you willing to travel for a pickup?" />
                                    <span>{{ form.travel_distance }} miles</span>
                                    <input id="travel_distance" type="range" class="mt-1 block w-full" v-model="form.travel_distance" min="1" max="50" />
                                    <jet-input-error :message="form.error('travel_distance')" class="mt-2" />
                                </div>
                            </template>

                        </template>

                        <template #actions>
                            <jet-action-message :on="form.recentlySuccessful" class="mr-3">
                                Saved.
                            </jet-action-message>

                            <jet-button :class="{ 'opacity-25': form.processing || !form.notify_food_drive }" :disabled="form.processing || !form.notify_food_drive">
                                Subscribe to Notifications
                            </jet-button>
                        </template>
                    </jet-form-section>
                    <gmap-map
                        :center="{lat: 26.5408576, lng: -80.0819594}"
                        :zoom="13"
                        style="width: 100%; height: 300px"
                    >
                        <gmap-marker
                            :key="index"
                            v-for="(m, index) in markers"
                            :position="m.location"
                            :clickable="true"
                            :draggable="true"
                            @click="center=m.location"
                            :icon="icon"
                        ></gmap-marker>
                    </gmap-map>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout'
    import JetButton from '@/Jetstream/Button'
    import JetFormSection from '@/Jetstream/FormSection'
    import JetInput from '@/Jetstream/Input'
    import JetInputError from '@/Jetstream/InputError'
    import JetLabel from '@/Jetstream/Label'
    import JetActionMessage from '@/Jetstream/ActionMessage'
    import JetSecondaryButton from '@/Jetstream/SecondaryButton'

    export default {
        components: {
            AppLayout,
            JetActionMessage,
            JetButton,
            JetFormSection,
            JetInput,
            JetInputError,
            JetLabel,
            JetSecondaryButton,
        },
        props: ['user'],
        data() {
            return {
                form: this.$inertia.form({
                    '_method': 'PUT',
                    name: {}.name,
                    email: {}.email,
                    phone: {}.phone,
                    volunteer_preferences: {}.volunteer_preferences,
                    notify_food_drive: {}.notify_food_drive,
                    street_address: {}.street_address,
                    city: {}.city,
                    state: {}.state,
                    postal_code: {}.postal_code,
                    travel_distance: {}.travel_distance || 2,
                    photo: null,
                }, {
                    bag: 'updateProfileInformation',
                    resetOnSuccess: false,
                }),
                icon: {
                    path: "M-20,0a20,20 0 1,0 40,0a20,20 0 1,0 -40,0",
                    fillColor: '#ff7a03',
                    fillOpacity: 1,
                    anchor: {x: 0, y: 0},
                    strokeWeight: 0,
                    scale: 0.2
                },
                markers: this.$page.places.filter(p => p.location)
                    .map(p => {
                        return {
                            location: {
                                lat: p.location.coordinates[1],
                                lng: p.location.coordinates[0],
                            }
                        }
                    })
            }
        },
        methods: {
            updateProfileInformation() {
                this.form.post(route('user-profile-information.update'), {
                    preserveScroll: true
                });
            }
        }
    }
</script>
