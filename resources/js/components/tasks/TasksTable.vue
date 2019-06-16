<template>
    <div class="flex row justify-content-between">
        <new-task @task-added="refresh" :workspace="workspace"></new-task>
        
        <table class="table col-md-10">
            <thead>
                <tr>
                    <th scope="col">Task</th>
                    <th scope="col">Creator</th>
                    <th scope="col">Person responsible</th>
                    <th scope="col">Status</th>
                    <th scope="col">Start date</th>
                    <th scope="col">Finish date</th>
                </tr>
            </thead>
            <tbody> 
                <tr v-for="task in items">
                    <td v-text="task.name"></td>
                    <td v-text="task.creator.full_name"></td>
                    <td v-text="task.assignee.full_name"></td>
                    <td v-text="task.status"></td>
                    <td v-text="formattedDate(task.start_date)"></td>
                    <td v-text="formattedDate(task.finish_date)"></td>
                </tr>
            </tbody>
        </table>        
    </div>
</template>

<script>
	import moment from 'moment';
    import NewTask from './NewTask.vue';

    export default {
      props: ['workspace'],

      data() {
        return {
            items: [],
        }
    },

    methods: {
        formattedDate(date) {
            return moment(date).format("LL");
        },

        fetchTasks() {
            axios
            .get(`/workspaces/${this.workspace.id}/tasks`)
            .then(response => {
                // handle success
                // assign tasks to the items data.
                this.items = response.data[1];
            })
            .catch(function (error) {
                // handle error
                console.log(error);
            });
        },

        refresh() {
            this.fetchTasks();
        },    
    },

    created() {
        this.fetchTasks();
    },
}
</script>