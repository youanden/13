<template>
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-4">
                <div class=" rounded-md bg-green-50 p-4 " v-if="$page.message">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <!-- Heroicon name: check-circle -->
                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm leading-5 font-medium text-green-800">
                                {{ $page.message }}
                            </p>
                        </div>
                        <div class="ml-auto pl-3">
                            <div class="-mx-1.5 -my-1.5">
                                <button @click="$page.message = null" class="inline-flex rounded-md p-1.5 text-green-500 hover:bg-green-100 focus:outline-none focus:bg-green-100 transition ease-in-out duration-150" aria-label="Dismiss">
                                    <!-- Heroicon name: x -->
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 ">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <form class="p-6" @submit.prevent="createEvent">
                        <div>
                            <div class="">
                                <div>
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                                        Event Information
                                    </h3>
                                </div>
                                <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                    <div class="sm:col-span-3">
                                        <label for="name"
                                               class="block text-sm font-medium leading-5 text-gray-700">
                                            Name
                                        </label>
                                        <div class="mt-1 rounded-md shadow-sm">
                                            <input id="name"
                                                   class="form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5"
                                                   v-model="form.name" >
                                        </div>
                                    </div>

                                    <div class="sm:col-span-3">
                                        <label for="place" class="block text-sm font-medium leading-5 text-gray-700">
                                            Place / Venue
                                        </label>
                                        <div class="mt-1 rounded-md shadow-sm">
                                            <select id="place"
                                                    class="form-select block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5"
                                                    v-model="form.place_id">
                                                <option selected value="">Select a Location</option>
                                                <option v-for="place in $page.places" :value="place.id">
                                                    {{ place.name }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>


                                    <div class="sm:col-span-3">
                                        <label for="name"
                                               class="block text-sm font-medium leading-5 text-gray-700">
                                            Start Time
                                        </label>
                                        <div class="mt-1 rounded-md shadow-sm">
                                            <input id="start_time"
                                                   type="datetime-local"
                                                   class="form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5"
                                                   v-model="form.start_time" >
                                        </div>
                                    </div>

                                    <div class="sm:col-span-3">
                                        <label for="name"
                                               class="block text-sm font-medium leading-5 text-gray-700">
                                            End Time
                                        </label>
                                        <div class="mt-1 rounded-md shadow-sm">
                                            <input id="end_time"
                                                   type="datetime-local"
                                                   class="form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5"
                                                   v-model="form.end_time" >
                                        </div>
                                    </div>

                                    <div class="sm:col-span-6">
                                        <label for="address"
                                               class="block text-sm font-medium leading-5 text-gray-700">
                                            Address
                                        </label>
                                        <div class="mt-1 rounded-md shadow-sm">
                                            <input id="address"
                                                   class="form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5"
                                                   disabled :value="form.place_id && $page.places[form.place_id].hasOwnProperty('address') && $page.places[form.place_id]['address']" >
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="mt-8 border-t border-gray-200 pt-8">
                                <div class="mt-6">
                                    <fieldset class="mt-6">
                                        <legend class="text-base font-medium text-gray-900">
                                            Notification Settings
                                        </legend>
                                        <p class="text-sm leading-5 text-gray-500">
                                            Decide how your event gets sent
                                        </p>
                                        <div class="mt-4">
                                            <div class="flex items-center">
                                                <input id="push_everything" name="push_notifications" type="radio"
                                                       class="form-radio h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
                                                <label for="push_everything" class="ml-3">
                                                    <span class="block text-sm leading-5 font-medium text-gray-700">Email and SMS</span>
                                                </label>
                                            </div>
                                            <div class="mt-4 flex items-center">
                                                <input id="push_email" name="push_notifications" type="radio"
                                                       class="form-radio h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
                                                <label for="push_email" class="ml-3">
                                                    <span class="block text-sm leading-5 font-medium text-gray-700">Email Only</span>
                                                </label>
                                            </div>
                                            <div class="mt-4 flex items-center">
                                                <input id="push_sms_only" name="push_notifications" type="radio"
                                                       class="form-radio h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
                                                <label for="push_sms_only" class="ml-3">
                                                    <span class="block text-sm leading-5 font-medium text-gray-700">SMS Only</span>
                                                </label>
                                            </div>
                                            <div class="mt-4 flex items-center">
                                                <input id="push_nothing" name="push_notifications" type="radio"
                                                       class="form-radio h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
                                                <label for="push_nothing" class="ml-3">
                                                    <span class="block text-sm leading-5 font-medium text-gray-700">No Notifications / Draft Event</span>
                                                </label>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>
                        </div>
                        <div class="mt-8 border-t border-gray-200 pt-5">
                            <div class="flex justify-end">
                                <span class="inline-flex rounded-md shadow-sm">
                                    <button type="button"
                                        class="py-2 px-4 border border-gray-300 rounded-md text-sm leading-5 font-medium text-gray-700 hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-50 active:text-gray-800 transition duration-150 ease-in-out">
                                    Cancel
                                    </button>
                                </span>
                                <span class="ml-3 inline-flex rounded-md shadow-sm">
                                    <button type="submit"
                                        class="inline-flex justify-center py-2 px-4 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition duration-150 ease-in-out">
                                    Save & Notify
                                    </button>
                                </span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout'

export default {
    components: {
        AppLayout
    },
    data () {
        return {
            form: this.$inertia.form({
                '_method': 'PUT',
                place_id: '',
                name: '',
                start_time: '2020-10-25T09:00',
                end_time: '2020-10-25T20:00'
            }, {
                bag: 'createEvent',
                resetOnSuccess: false,
            })
        }
    },
    methods: {
        createEvent() {
            this.form.post(route('events.create'), {
                preserveScroll: true
            });
        }
    }
}
</script>
