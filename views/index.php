<div id="idAllTubes">
    <section id="summaryTable">
        <div class="row">
            <div class="col-sm-12">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th  name="current-jobs-ready" title="number of jobs in the ready queue in this tube">current-jobs-ready</th>
                            <th  name="current-jobs-delayed" title="number of delayed jobs in this tube">current-jobs-delayed</th>
                            <th  name="current-jobs-working" title="number of running jobs in this tube">current-jobs-working</th>
                            <th  name="current-jobs-buried" title="number of buried jobs in this tube">current-jobs-buried</th>
                            <th  name="current-jobs-ended" title="number of ended jobs in this tube">current-jobs-ended</th>
                            <th  name="total-jobs" title="total jobs">total-jobs</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($queues as $qi) { ?>
                            <tr>
                                <td name="pause-time-left"><a href="?q=<?= $qi['queue'] ?>"><?= $qi['queue'] ?></a></td>
                                <td><?= $qi['ready'] ?></td>
                                <td><?= $qi['delayed'] ?></td>
                                <td><?= $qi['working'] ?></td>
                                <td><?= $qi['buried'] ?></td>
                                <td><?= $qi['ended'] ?></td>
                                <td><?= $qi['total'] ?></td>
                                <td><a class="btn btn-xs btn-danger" href="#">Purge</a></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>