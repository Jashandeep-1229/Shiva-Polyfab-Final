<div class="modal-content">
    <div class="modal-header">
        <h4 class="modal-title" id="mySmallModalLabel">View Job Card</h4>
        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close" data-bs-original-title="" title=""></button>
    </div>
    <div class="modal-body dark-modal">
        <table class="table table-bordered">
            <tr>
                <th>Name Of Job</th>
                <td>{{$job_card->name_of_job}}</td>  
                <th>BOPP</th>
                <td>{{$job_card->bopp->name ?? ''}}</td>
                <th colspan="2">File</th>
            </tr>
            <tr>
                <th>Job Type</th>
                <td>{{$job_card->job_type}}</td>
                 <th>Fabric</th>
                <td>{{$job_card->fabric->name ?? ''}}</td>
                <td rowspan="5" width="100px">
                    <a href="{{asset('uploads/job_card/'.$job_card->file_upload)}}" target="_blank">
                    <img width="150px" src="{{asset('uploads/job_card/'.$job_card->file_upload)}}" alt="">
                    </a>
                </td>
            </tr>
            <tr>
                <th>No of Pieces</th>
                <td>{{$job_card->no_of_pieces}}</td>
                 <th>Loop Color</th>
                <td>{{$job_card->loop_color ?? ''}}</td>
            </tr>
            <tr>
                <th>Job Card Date</th>
                <td>{{date('d-m-Y', strtotime($job_card->job_card_date))}}</td>
                 <th>Dispatch Date</th>
                <td>{{date('d-m-Y', strtotime($job_card->dispatch_date))}}</td>
            </tr>
            <tr>
                <th>Order Send For</th>
                <td>{{$job_card->order_send_for}}</td>
                <th>Status</th>
                <td>{{$job_card->job_card_process}}</td>
            </tr>
            <tr>
                <th>Addtional Note</th>
                <td colspan="3">{{$job_card->remarks}}</td>
            </tr>
            
        </table>

    </div>
    <div class="modal-footer text-end">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    </div>
</div>
