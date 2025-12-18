import PrimeVue from 'primevue/config';

// Services
import ToastService from 'primevue/toastservice';
import ConfirmationService from 'primevue/confirmationservice';

// Components
import Button from 'primevue/button';
import Card from 'primevue/card';
import Dropdown from 'primevue/dropdown';
import Tag from 'primevue/tag';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Avatar from 'primevue/avatar';
import Timeline from 'primevue/timeline';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Badge from 'primevue/badge';
import Menu from 'primevue/menu';
import Sidebar from 'primevue/sidebar';

import Toast from 'primevue/toast';
import ConfirmDialog from 'primevue/confirmdialog';

export default {
    install(app) {
        // PrimeVue core
        app.use(PrimeVue);

        // Services
        app.use(ToastService);
        app.use(ConfirmationService);

        // Components
        app.component('Button', Button);
        app.component('Card', Card);
        app.component('Dropdown', Dropdown);
        app.component('Tag', Tag);
        app.component('DataTable', DataTable);
        app.component('Column', Column);
        app.component('Avatar', Avatar);
        app.component('Timeline', Timeline);
        app.component('InputText', InputText);
        app.component('Dialog', Dialog);
        app.component('Badge', Badge);
        app.component('Menu', Menu);
        app.component('Sidebar', Sidebar);
        app.component('Toast', Toast);
        app.component('ConfirmDialog', ConfirmDialog);
    }
};
