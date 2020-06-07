
$(document).ready(function () {

    $("#searchApiGo").click(function () {
        var value = $("#searchApi").val()
        $.ajax({
            type: 'POST',
            url: "/search/" + value,
            data: {
                value: value
            },
            success: function (data) {
                searchApi(data);
            }
        });
    });

    function searchApi(data) {
        $("#getStartedMovies").html(data.data);
        $(".addMovie").click(
            function deleteMovie() {
                var id = $(this).data("id");
                var card = $(this).closest("div .movieCard");
                $.ajax({
                    type: 'POST',
                    url: "/add/" + id,
                    data: {
                        id: id
                    },
                    success: function (data) {
                        card.remove();
                        $("#movie_count").empty().append(data.moviesLeft);
                    },
                    error: function (data) {
                        card.closest("div .movieCard").empty().append('<p class="danger">You already selected this movie</p>');
                    }
                });
            }
        );
    }


    $(".personalRate").click(function () {
        var row = $(this).closest("tr");
        var movieId = $(this).data("id");
        var status = $(this).data("status");
        $.ajax({
            type: 'POST',
            url: "/movie/history/" + movieId + "/" + status,
            data: {
                movieId: movieId,
                status: status
            },
            success: function (data) {
                row.remove();
                if ((typeof data.achievements !== null) && (typeof data.achievements !== 'undefined') && data.achievements.length) {
                    $(".modal-title").empty().append('You gained an Achievement!');
                    data.achievements.forEach(displayFunciton);
                    function displayFunciton(achievement) {
                        $(".modal-body").append('<p><b>' + achievement + '</b></p>');
                    }
                    $("#achievementNotify").show()
                    $("#achievementNotify").on('click', '.close', function () {
                        $("#achievementNotify").hide();
                    });
                }
            },
            error: function (data) {
                $(".modal-title").empty().append('Notification');
                $("#achievementNotify").show();
                $(".modal-body").empty().append('You have to wait 2 hours before watching another movie. <br/>You can click seen, but it won\'t count as a watched movie');
                $("#achievementNotify").on('click', '.close', function () {
                    $("#achievementNotify").hide();
                });
            }
        });
    });

    $("#movieTable").dataTable();
});
