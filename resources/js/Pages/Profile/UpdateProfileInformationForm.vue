<template>
    <jet-form-section @submitted="updateProfileInformation">
        <template #title>
            Profile Information
        </template>

        <template #description>
            Update your account's profile information and email address.
        </template>

        <template #form>
            <!-- Profile Photo -->
            <div class="col-span-6 sm:col-span-4" v-if="$page.jetstream.managesProfilePhotos">
                <!-- Profile Photo File Input -->
                <input type="file" class="hidden"
                            ref="photo"
                            @change="updatePhotoPreview">

                <jet-label for="photo" value="Photo" />

                <!-- Current Profile Photo -->
                <div class="mt-2" v-show="! photoPreview">
                    <img :src="user.profile_photo_url" alt="Current Profile Photo" class="rounded-full h-20 w-20 object-cover">
                </div>

                <!-- New Profile Photo Preview -->
                <div class="mt-2" v-show="photoPreview">
                    <span class="block rounded-full w-20 h-20"
                          :style="'background-size: cover; background-repeat: no-repeat; background-position: center center; background-image: url(\'' + photoPreview + '\');'">
                    </span>
                </div>

                <jet-secondary-button class="mt-2 mr-2" type="button" @click.native.prevent="selectNewPhoto">
                    Select A New Photo
                </jet-secondary-button>

                <jet-secondary-button type="button" class="mt-2" @click.native.prevent="deletePhoto" v-if="user.profile_photo_path">
                    Remove Photo
                </jet-secondary-button>

                <jet-input-error :message="form.error('photo')" class="mt-2" />
            </div>

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

            <!-- I'd like to be contacted to help volunteer -->
            <div class="col-span-6 sm:col-span-4">
                <jet-label class="mb-2">Notification Preferences</jet-label>
                <div class="relative flex items-start">
                    <div class="flex items-center h-5">
                        <input id="volunteer_preferences" type="checkbox" class="mt-1 block form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out" v-model="form.volunteer_preferences" />
                    </div>
                    <div class="ml-3 text-sm leading-5">
                        <jet-label for="volunteer_preferences" class="font-medium text-gray-700" value="Volunteering" />
                        <p class="text-gray-500">I'd like to be contacted to help volunteer</p>
                    </div>
                    <jet-input-error :message="form.error('volunteer_preferences')" class="mt-2" />
                </div>
            </div>

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
        </template>

        <template #actions>
            <jet-action-message :on="form.recentlySuccessful" class="mr-3">
                Saved.
            </jet-action-message>

            <jet-button :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Save
            </jet-button>
        </template>
    </jet-form-section>
</template>

<script>
    import JetButton from '@/Jetstream/Button'
    import JetFormSection from '@/Jetstream/FormSection'
    import JetInput from '@/Jetstream/Input'
    import JetInputError from '@/Jetstream/InputError'
    import JetLabel from '@/Jetstream/Label'
    import JetActionMessage from '@/Jetstream/ActionMessage'
    import JetSecondaryButton from '@/Jetstream/SecondaryButton'

    export default {
        components: {
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
                    name: this.user.name,
                    email: this.user.email,
                    phone: this.user.phone,
                    volunteer_preferences: this.user.volunteer_preferences,
                    notify_food_drive: this.user.notify_food_drive,
                    photo: null,
                }, {
                    bag: 'updateProfileInformation',
                    resetOnSuccess: false,
                }),

                photoPreview: null,
            }
        },

        methods: {
            updateProfileInformation() {
                if (this.$refs.photo) {
                    this.form.photo = this.$refs.photo.files[0]
                }

                this.form.post(route('user-profile-information.update'), {
                    preserveScroll: true
                });
            },

            selectNewPhoto() {
                this.$refs.photo.click();
            },

            updatePhotoPreview() {
                const reader = new FileReader();

                reader.onload = (e) => {
                    this.photoPreview = e.target.result;
                };

                reader.readAsDataURL(this.$refs.photo.files[0]);
            },

            deletePhoto() {
                this.$inertia.delete(route('current-user-photo.destroy'), {
                    preserveScroll: true,
                }).then(() => {
                    this.photoPreview = null
                });
            },
        },
    }
</script>
