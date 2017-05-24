<template>
    <main-layout>
        <loader v-if="files === null">
            <p>{{ 'ui.app.loading' | translate }}</p>
        </loader>

        <div v-else-if="files.length === 0">
            <p>{{ 'ui.app.empty' | translate }}</p>
        </div>

        <section class="package">
            <div class="inside">
                <div class="about">
                    <h1>prod-2017-01-03.log</h1>
                </div>
                <div class="release">
                    <div class="version">
                        <strong>16.5 MB</strong>
                        <time>03.01.2017 16:00</time>
                    </div>
                </div>
                <fieldset class="actions">
                    <button class="uninstall">Delete</button>
                </fieldset>
            </div>
        </section>
    </main-layout>
</template>

<script>
    import api from '../../api';

    import MainLayout from '../layouts/Main';
    import Loader from '../fragments/Loader';

    export default {
        components: { MainLayout, Loader },
        data: () => ({
            files: null,
        }),

        methods: {
            listFiles() {
                api.getLogFiles().then((files) => {
                    this.files = files;
                });
            },
        },

        created() {
            this.listFiles();
        },
    };
</script>
