<?php
require_once(dirname(dirname(__FILE__)).'../../config.php');

global $CFG, $DB, $OUTPUT, $USER;
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('pluginname', 'block_medicinereports'));
$PAGE->set_heading(get_string('coursereport', 'block_medicinereports'));
$PAGE->navbar->ignore_active();
$PAGE->requires->jquery();
echo $OUTPUT->header();
echo '<script src="js/datatable.js"></script>';

//get user list

if (is_siteadmin()) {
    $sql = "select * from {user} where id<> $USER->id && confirmed = 1 && suspended= 0 ORDER BY trim(firstname),trim(lastname)";
    $users = $DB->get_records_sql($sql);
    $studentopt = "<option value='0'>" . get_string('select') . "</option>";
    foreach ($users as $user) {
        $studentopt .= "<option value='" . $user->id . "'>$user->firstname $user->lastname</option>";
    }
} else {
    $myid = $USER->id;
    $studentopt .= "<option selected value='" . $myid . "'>" . $USER->firstname . " " . $USER->lastname . "</option>";
}



?>

    <style>
        th.dt-center, td.dt-center { text-align: center; }
    </style>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css
">
    <div class="container">
        <div class="row col-md-12">

            <div class="form-group" style="<?php if (!is_siteadmin()) {echo 'display:none;';} ?>" >
                <label for="school_id" style="display: inline-block;"><?=get_string('selectstudent','block_medicinereports')?></label>
                <select class="form-control" id="student_id" >
                    <?=$studentopt?>
                </select>
            </div>

            <table id="coursereport" class="table table-bordered table-responsive-md">
                <thead>
                <tr>
                    <th>Course Name</th>
                    <th>Course Category </th>
                    <th class="dt-center">Clock Hrs</th>
                    <th class="dt-center">Certificate Issued</th>
                    <th class="dt-center">Licentiate of Natural Medicine</th>
                    <th class="dt-center">DMM (Doctor Monastic Medicine)</th>
                    <th class="dt-center">DNM (Doctor Natural Medicine)</th>
                    <th class="dt-center">OMD (Doctor Oriental Medicine)</th>
                    <th class="dt-center">DATIM (Indigenous & Traditional Medicine)</th>
                    <th class="dt-center">DHM (Doctor Homeopathic Medicine)</th>
                    <th class="dt-center">DD (Doctor of Dietetics)</th>
                    <th class="dt-center">Doctor of Arts</th>
                    <th class="dt-center">Ph.D.</th>
                    <th class="dt-center">Residency Hours</th>
                    <th class="dt-center">Awards, Accolades</th>
                </tr>
                </thead>
                <tfoot>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                </tfoot>
                <tbody>


                </tbody>

            </table>
        </div>

    </div>

    <script type="application/javascript">
        //    get all constants by school

        var ft;
        $(document).ready(function($){

            ft = $('#coursereport').DataTable({
                "serverSide": true,
                'processing': true,
                "paging":   false,


                'ajax': {
                    'url':'customajax.php?ajax_request=courselist',
                    'data': function(data){
                        // Read values
                        var student_id = $('#student_id').val();
                        // Append to data
                        data.student = student_id;
                    }
                },



                "columns": [


                    { "data": "course"},
                    { "data": "category"},
                    { "data": "clockhrs"},
                    { "data": "timecompleted"},
                    { "data": "lnm"},
                    { "data": "dmm"},
                    { "data": "dnm"},
                    { "data": "omd"},
                    { "data": "datim"},
                    { "data": "dma"},
                    { "data": "dd"},
                    { "data": "da"},
                    { "data": "phd"},
                    { "data": "reshours"},
                    { "data": "awards"}
                ],

                'columnDefs': [
                    {
                        'targets': [0, 1], // column or columns numbers
                        'className': 'dt-left'
                    },
                    {
                        'targets': [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14], // column or columns numbers
                        'className': 'dt-center'
                    },
                    { "orderable": false, "targets": [2,3,4,5,6,7,8,9,10,11,12,13,14] },
                ],


                // "footerCallback": function ( row, data, start, end, display ) {
                //     var api = this.api(), data;
                //
                //     // converting to interger to find total
                //     var intVal = function ( i ) {
                //         return typeof i === 'string' ?
                //             i.replace(/[\$,]/g, '')*1 :
                //             typeof i === 'number' ?
                //                 i : 0;
                //     };
                //
                //     var forTotal = api
                //         .column( 4)
                //         .data()
                //         .reduce( function (a, b) {
                //
                //             // console.log($(b).map(function() {
                //             //     return $(this).val()[1];
                //             // }))
                //
                //             return intVal(a) + intVal(b);
                //         }, 0 );
                //
                //     var thuTotal = api
                //         .column( 9)
                //         .data()
                //         .reduce( function (a, b) {
                //             return intVal(a) + intVal(b);
                //         }, 0 );
                //
                //     var friTotal = api
                //         .column( 10 )
                //         .data()
                //         .reduce( function (a, b) {
                //             return intVal(a) + intVal(b);
                //         }, 0 );
                //
                //     // Update footer by showing the total with the reference of the column index
                //
                //
                // },
                "drawCallback": function(settings) {

                    var alldata = settings.json;
                    var api = this.api(), alldata;
                    var total = alldata.total;
                    console.log(total.awardstotal);

                    $( api.column( 0 ).footer() ).html('Total');
                    $( api.column( 2 ).footer() ).html(total.clockhourstotal);
                    $( api.column( 4 ).footer() ).html(total.licentiatetotal);
                    $( api.column( 5 ).footer() ).html(total.dmmtotal);
                    $( api.column( 6 ).footer() ).html(total.dnmtotal);
                    $( api.column( 7 ).footer() ).html(total.omdtotal);
                    $( api.column( 8 ).footer() ).html(total.datimtotal);
                    $( api.column( 9 ).footer() ).html(total.dmatotal);
                    $( api.column( 10 ).footer() ).html(total.ddtotal);

                    $( api.column( 11 ).footer() ).html(total.datotal);
                    $( api.column( 12).footer() ).html(total.phdtotal);
                    $( api.column( 13 ).footer() ).html(total.resourcehourstotal);

                    $( api.column( 14 ).footer() ).html(total.awardstotal);


                    //do whatever
                },

                'dom': 'lBfrtipF',

                'buttons': [
                    {
                        extend: 'excel',
                        charset: 'utf-8',
                        footer: true,
                        bom: 'true'
                    },
                    {
                        extend: 'pdf',
                        orientation: 'landscape',
                        pageSize: 'LEGAL',
                        footer: true,
                        charset: 'utf-8',
                        bom: 'true'
                    },
                    {
                        extend: 'csv',
                        charset: 'utf-8',
                        footer: true,
                        bom: 'true',
                    }
                ],

                "language": {
                    "lengthMenu": $('#lengthMenu').val(),
                    "zeroRecords": $('#zeroRecords').val(),
                    "info": $('#info').val(),
                    "infoEmpty": $('#infoEmpty').val(),
                    "infoFiltered": $('#infoFiltered').val(),
                    "search": $('#search').val(),

                    "paginate": {
                        "next": $('#next').val(),
                        "previous":$('#previous').val(),
                    }
                },

            });
            $('#student_id').change(function(){
                ft.draw();
            });






        });



        function boxDisable(e, t) {
            if (t.is(':checked')) {
                alert('checked');
            } else {

                alert('unchecked');
            }
        }


        function updateResourceHours(courseid,userid,hours){
            if(courseid != '' && userid != ''){



                $.ajax({
                    'url':'customajax.php?ajax_request=updateresourcehours',
                    type: 'get',
                    data: {courseid: courseid,userid: userid, hours: hours},
                    success: function(response){
                        console.log('response : ' + response);
                        ft.draw(false);
                    },
                    error: function(response){
                        console.log('error: ' + JSON.stringify(response) );
                    }
                });
            }
        }

        function isNumberKey(evt){
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57))
                return false;

            return true;
        }
        function updateValuecheck(fieldname,courseid,userid,value,t){
            if(courseid != '' && userid != ''){
                //
                if (t.is(':checked')) {
                    value =  value;
                }else{

                    value =  '';
                }



                $.ajax({
                    'url':'customajax.php?ajax_request=updatevalue',
                    type: 'get',
                    data: {fieldname: fieldname,courseid: courseid,userid: userid, value: value},
                    success: function(response){
                        console.log('response : ' + response);
                        ft.draw(false);
                    },
                    error: function(response){
                        console.log('error: ' + JSON.stringify(response) );
                    }
                });
            }
        }
        function updateValue(fieldname,courseid,userid,value){
            if(courseid != '' && userid != ''){
                //




                $.ajax({
                    'url':'customajax.php?ajax_request=updatevalue',
                    type: 'get',
                    data: {fieldname: fieldname,courseid: courseid,userid: userid, value: value},
                    success: function(response){
                        console.log('response : ' + response);
                        ft.draw(false);
                    },
                    error: function(response){
                        console.log('error: ' + JSON.stringify(response) );
                    }
                });
            }
        }
    </script>


    <script src=" https://cdn.datatables.net/buttons/1.5.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.print.min.js"></script>

<?php

echo $OUTPUT->footer();
?>