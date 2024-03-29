@extends('admin.admin_app')

@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card-box table-responsive">


                            <div class="container-lg mt-4">
                                <div class="container text-white">
                                    <div class="row justify-content-center">
                                        <div class="col">
                                            <div class="card-md">
                                                <div class="card-header text-center h3">Ophim Crawler</div>
                                                <div class="card-body">
                                                    <div class="input-group mb-3">
                                                        <input type="hidden" id="plugin_path" name="plugin_path"
                                                            value="">
                                                    </div>
                                                    <div id="alert-box" class="alert" style="display: none;"
                                                        role="alert"></div>
                                                    <div class="input-group  mb-4">
                                                        <span class="input-group-text">Nhập vào JSON API</span>
                                                        <input type="text" class="form-control" id="jsonapi-url"
                                                            value="https://ophim1.com/danh-sach/phim-moi-cap-nhat"
                                                            placeholder="https://ophim1.com/danh-sach/phim-moi-cap-nhat">
                                                        <button class="btn btn-primary" type="button"
                                                            id="ophim-api-check">Kiểm Tra</button>
                                                    </div>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text">Thu thập từ Page</span>
                                                        <input type="number" class="form-control" name="page-from"
                                                            placeholder="số page (1)">
                                                        <span class="input-group-text">Đến Page</span>
                                                        <input type="number" class="form-control" name="page-to"
                                                            placeholder="số page (10)">
                                                        <button class="btn btn-primary" type="button"
                                                            id="page-from-to">Thực Hiện</button>
                                                    </div>
                                                    <p class="fst-italic fs-6 my-1">Hoặc crawl theo link film API chi tiết:
                                                    </p>
                                                    <div class="input-group mb-2">
                                                        <span class="input-group-text">Nhập vào link phim</span>
                                                        <input type="text" class="form-control" id="onemovie-link"
                                                            placeholder="https://ophim1.com/phim/ngoi-truong-xac-song"
                                                            value="https://ophim1.cc/phim/bungo-stray-dogs-4">
                                                        <button class="btn btn-success" type="button"
                                                            id="onemovie-crawl">Thu Thập Ngay</button>
                                                    </div>
                                                </div>
                                                <div id="content" class="card-body pt-0">

                                                    <div class="card-title">Thông Tin Nguồn Phim: </div>
                                                    <ul id="server-info" class="list-group">
                                                        <li
                                                            class="list-group-item d-flex justify-content-between align-items-center">
                                                            Tổng số bộ phim
                                                            <span id="movies-total"
                                                                class="badge bg-primary rounded-pill"></span>
                                                        </li>
                                                        <li
                                                            class="list-group-item d-flex justify-content-between align-items-center">
                                                            Tổng số page
                                                            <span id="last-page"
                                                                class="badge bg-primary rounded-pill"></span>
                                                        </li>
                                                        <li
                                                            class="list-group-item d-flex justify-content-between align-items-center">
                                                            Cập nhật hôm nay
                                                            <span id="update-today"
                                                                class="badge bg-primary rounded-pill"></span>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div id="movies-list" class="card-body" style="display: none;">
                                                    <div class="card-title" id="current-page-crawl">
                                                        <h4 id="h4-current-page" class="position-absolute">Page 1</h4>
                                                        <div class="d-grid gap-2 d-md-flex justify-content-md-end me-5">
                                                            <button id="pause-crawl" type="button"
                                                                class="btn btn-danger mr-2">Dừng</button>
                                                            <button id="resume-crawl" type="button"
                                                                class="btn btn-success">Tiếp tục</button>
                                                        </div>
                                                    </div>
                                                    <table class="table" id="movies-table">
                                                        <thead>
                                                            <tr>
                                                                <th scope="col">ID</th>
                                                                <th scope="col">Tên Phim</th>
                                                                {{-- <th scope="col">Thể Loại</th> --}}
                                                                <th scope="col">Cập nhật</th>
                                                                <th scope="col">Quá trình</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table>
                                                </div>
                                                <div class="card-footer">
                                                    <button id="roll-crawl" type="button"
                                                        class="btn btn-success position-absolute">Trộn Link</button>
                                                    <div class="d-grid d-md-flex justify-content-md-end">
                                                        <button id="selected-crawl" type="button"
                                                            class="btn btn-success mr-2">Thu Thập Page</button>
                                                        <button id="update-crawl" type="button"
                                                            class="btn btn-success mr-2">Thu Thập Hôm Nay</button>
                                                        <button id="full-crawl" type="button"
                                                            class="btn btn-primary">Thu Thập Toàn Bộ</button>
                                                    </div>
                                                </div>
                                                <div class="card-footer mt-4">
                                                    <h5>Tham gia nhóm trao đổi chia sẻ: <a target="_blank"
                                                            href="https://t.me/+QMfjBOtNpkZmNTc1">Ophim Telegram.</a></h5>
                                                    <p><a href="https://ophim1.cc/">Ophim.cc</a> là website dữ liệu phim
                                                        miễn phí vĩnh viễn. Cập nhật nhanh, chất lượng cao, ổn định và lâu
                                                        dài. Tốc độ phát cực nhanh với đường truyền băng thông cao, đảm bảo
                                                        đáp ứng được lượng xem phim trực tuyến lớn. Đồng thời giúp nhà phát
                                                        triển website phim giảm thiểu chi phí của các dịch vụ lưu trữ và
                                                        stream. <br />Hàng ngày chạy tools tầm 10 đến 20 pages đầu (tùy số
                                                        lượng phim được cập nhật trong ngày) để update tập mới hoặc thêm
                                                        phim mới!.<br />Trộn link vài lần để thay đổi thứ tự crawl & update.
                                                        Giúp tránh việc quá giống nhau về content của các website!.<br />
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            // DOM elements
            const buttonCheckApi = $("#ophim-api-check");
            const buttonRollCrawl = $("#roll-crawl");
            const buttonUpdateCrawl = $("#update-crawl");
            const buttonFullCrawl = $("#full-crawl");
            const buttonPageFromTo = $("#page-from-to");
            const buttonSelectedCrawl = $("#selected-crawl");
            const buttonPauseCrawl = $("#pause-crawl");
            const buttonResumeCrawl = $("#resume-crawl");
            const buttonOneCrawl = $("#onemovie-crawl");
            const alertBox = $("#alert-box");
            const moviesListDiv = $("#movies-list");
            const divCurrentPage = $("#current-page-crawl");
            const inputPageFrom = $("input[name=page-from]");
            const inputPageTo = $("input[name=page-to]");
            const dissmissBtn =
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
            // Variable
            let latestPageList = [];
            let fullPageList = [];
            let pageFromToList = [];
            let tempPageList = [];
            let tempMoviesId = [];
            let tempMovies = [];
            let tempHour = '';
            let apiUrl = '';
            let host = '';
            let isStopByUser = false;
            let maxPageTo = 0;

            // Disable crawl function if api url is not verify
            buttonRollCrawl.prop("disabled", true);
            buttonUpdateCrawl.prop("disabled", true);
            buttonFullCrawl.prop("disabled", true);
            buttonPageFromTo.prop("disabled", true);
            buttonSelectedCrawl.prop("disabled", true);

            // Check input api first
            buttonCheckApi.click(function(e) {
                e.preventDefault();
                apiUrl = $("#jsonapi-url").val();
                oneLink = new URL(apiUrl);
                let pathname = oneLink.pathname;
                host = oneLink.origin;
                if (!apiUrl) {
                    alertBoxShow();
                    alertBox.removeClass().addClass("alert alert-danger");
                    alertBox.html("JSON API không thể để trống");
                    return false;
                }
                $("#movies-table tbody").html('');
                moviesListDiv.hide();
                $(this).html(
                    `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...`
                );
                $.ajax({
                    type: "POST",
                    url: "{{ route('crawlophim.check') }}",
                    data: {
                        pathname: pathname,
                        origin: host
                    },
                    success: function(response) {
                        buttonCheckApi.html(`Kiểm Tra`);
                        let data = response;
                        if (data.code > 1) {
                            alertBoxShow();
                            alertBox.removeClass().addClass("alert alert-danger");
                            alertBox.html(data.message)
                        } else {
                            buttonRollCrawl.prop("disabled", false);
                            buttonRollCrawl.html("Trộn Link");
                            buttonUpdateCrawl.prop("disabled", false);
                            buttonFullCrawl.prop("disabled", false);
                            buttonPageFromTo.prop("disabled", false);
                            buttonSelectedCrawl.prop("disabled", false);
                            latestPageList = data.latest_list_page;
                            fullPageList = data.full_list_page.reverse();
                            maxPageTo = data.last_page;
                            $("#movies-total").html(data.total);
                            $("#last-page").html(data.last_page);
                            $("#update-today").html(data.update_today);
                            $("#type_list").children('div.removeable').remove();
                            $("#type_list").append(data.type_list);
                        }
                    },
                });
            });

            // Crawl one movie
            buttonOneCrawl.click(function(e) {
                e.preventDefault();
                $(this).html(
                    `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...`
                );
                let oneLink = $("#onemovie-link").val();
                if (!oneLink) {
                    alertBoxShow();
                    alertBox.removeClass().addClass("alert alert-danger");
                    alertBox.html("Link phim không thể để trống");
                    buttonOneCrawl.html("Thu Thập Ngay");
                    return false;

                }
                if (!isValidUrl(oneLink)) {
                    alertBoxShow();
                    alertBox.removeClass().addClass("alert alert-danger");
                    alertBox.html("Link phim không đúng");
                    buttonOneCrawl.html("Thu Thập Ngay");
                    return false;
                }
                oneLink = new URL(oneLink);
                let pathName = oneLink.pathname;
                let origin = oneLink.origin;
                $.ajax({
                    type: "POST",
                    url: "{{ route('crawlophim.crawl_one') }}",
                    data: {
                        api: pathName,
                        origin: origin,
                    },
                    success: function(response) {
                        let data = (response);
                        if (data.code > 1) {
                            alertBoxShow();
                            alertBox.removeClass().addClass("alert alert-danger");
                            alertBox.html(data.message);
                        } else {
                            alertBoxShow();
                            alertBox.removeClass().addClass("alert alert-success");
                            alertBox.html(data.message);
                        }
                        buttonOneCrawl.html("Thu Thập Ngay");
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        alert("Something went wrong");
                        buttonOneCrawl.html("Thu Thập Ngay");
                    }
                });
            });

            // Set page from to
            buttonPageFromTo.click(function(e) {
                e.preventDefault();
                alertBox.html('');
                alertBox.hide();
                let pageFrom = inputPageFrom.val();
                let pageTo = inputPageTo.val();
                if (pageTo > maxPageTo || pageFrom > pageTo || pageFrom <= 0 || pageTo <= 0 || pageFrom ==
                    null || pageTo == null) {
                    alertBoxShow();
                    alertBox.removeClass().addClass("alert alert-danger");
                    alertBox.html(`Có lỗi xảy ra khi crawl theo số page. ${pageFrom}  toi ${pageTo}`);
                    return;
                }
                let pages = [];
                for (let i = parseInt(pageFrom); i <= pageTo; i++) {
                    pages.push(i);
                }
                pageFromToList = pages.reverse();
                alertBoxShow();
                alertBox.removeClass().addClass("alert alert-success");
                alertBox.html(`Cập nhật số page thành công: ${pageFrom} tới ${pageTo}`);
            });

            // Crawl from pageFrom to pageTo
            buttonSelectedCrawl.click(function(e) {
                e.preventDefault();
                $("#movies-table").show();
                $(this).html(
                    `<span class="spinner-border" role="status" aria-hidden="true"></span> Loading...`
                );
                moviesListDiv.show();
                crawl_movies_page(pageFromToList);
            });

            // Update today's movies
            buttonUpdateCrawl.click(function(e) {
                e.preventDefault();
                $("#movies-table").show();
                $(this).html(
                    `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...`
                );
                crawl_movies_page(latestPageList);
            });

            // Crawl full movies
            buttonFullCrawl.click(function(e) {
                e.preventDefault();
                $("#movies-table").show();
                $(this).html(
                    `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...`
                );
                crawl_movies_page(fullPageList);
            });

            // Random crawl page
            buttonRollCrawl.click(function(e) {
                e.preventDefault();
                $(this).html(
                    `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...`
                );
                fullPageList.sort((a, b) => 0.5 - Math.random());
                latestPageList.sort((a, b) => 0.5 - Math.random());
                pageFromToList.sort((a, b) => 0.5 - Math.random());
                $(this).html('Trộn Link OK');
            });

            // Pause crawl
            buttonPauseCrawl.click(function(e) {
                e.preventDefault();
                isStopByUser = true;
                buttonResumeCrawl.prop("disabled", false);
                buttonPauseCrawl.prop("disabled", true);
            });

            // Resume crawl
            buttonResumeCrawl.click(function(e) {
                e.preventDefault();
                isStopByUser = false;
                buttonPauseCrawl.prop("disabled", false);
                buttonResumeCrawl.prop("disabled", true);
                crawl_movie_by_id(tempMoviesId, tempMovies);
            });

            // Crawl movies page
            const crawl_movies_page = (pagesList) => {
                if (pagesList.length == 0) {
                    alertBoxShow();
                    alertBox.removeClass().addClass("alert alert-success");
                    alertBox.html('Hoàn tất thu thập phim!');
                    moviesListDiv.hide();
                    buttonRollCrawl.prop("disabled", false);
                    buttonUpdateCrawl.prop("disabled", false);
                    buttonFullCrawl.prop("disabled", false);
                    buttonSelectedCrawl.html("Thu Thập Page");
                    buttonUpdateCrawl.html("Thu Thập Hôm Nay");
                    buttonFullCrawl.html("Thu Thập Toàn Bộ");
                    tempPageList = [];
                    pageFromToList = [];
                    return;
                }
                let currentPage = pagesList.shift();
                tempPageList = pagesList;
                tempHour = '';

                $.ajax({
                    type: "POST",
                    url: "{{ route('crawlophim.get_movies_page') }}",
                    data: {
                        api: apiUrl,
                        param: `page=${currentPage}`,
                    },
                    beforeSend: function() {
                        divCurrentPage.show();
                        $("#current-page-crawl h4").html(`Page ${currentPage}`);
                        buttonRollCrawl.prop("disabled", true);
                        buttonSelectedCrawl.prop("disabled", true);
                        buttonUpdateCrawl.prop("disabled", true);
                        buttonFullCrawl.prop("disabled", true);
                        buttonResumeCrawl.prop("disabled", true);
                        moviesListDiv.show();
                    },
                    success: function(response) {
                        let data = response;
                        if (data.code > 1) {
                            alertBoxShow();
                            alertBox.removeClass().addClass("alert alert-danger");
                            alertBox.html(data.message);
                        } else {
                            let mIdList = [];
                            $.each(data.movies, function(idx, movie) {
                                mIdList.push(movie.slug);
                            });
                            crawl_movie_by_id(mIdList, data.movies);
                        }
                    },
                });
            };

            // Crawl movie by Id
            const crawl_movie_by_id = (ids, movies) => {
                if (isStopByUser) {
                    return;
                }
                display_movies(movies);
                let id = ids.shift();
                tempMoviesId = ids;
                tempMovies = movies;
                if (id == null) {
                    $("#movies-table tbody").html('');
                    crawl_movies_page(tempPageList);
                    return;
                }
                $.ajax({
                    type: "POST",
                    url: "{{ route('crawlophim.crawl_one') }}",
                    data: {
                        api: '/phim/' + id,
                        origin: host
                    },
                    success: function(response) {
                        let data = (response);
                        console.log(data.message);
                        if (data.code > 1) {
                            alertBoxShow();
                            alertBox.removeClass().addClass("alert alert-danger");
                            alertBox.html(data.message);
                            update_movies(id, ' Không cần cập nhật');
                        } else {
                            alertBoxShow();
                            alertBox.removeClass().addClass("alert alert-success");
                            alertBox.html(data.message)
                            update_movies(id, ' Thành công');
                        }
                        crawl_movie_by_id(ids);
                    }
                });
            };

            // Display movies list
            const display_movies = (movies) => {
                let trHTML = '';
                $.each(movies, function(idx, movie) {
                    $id = movie._id.substr(20);
                    $time = movie.modified.time.split("T");
                    trHTML +=
                        `<tr id="${movie.slug}">
                    <td>${$id}</td>
                    <td>${movie.name}</td>
                    <td>${$time[0]}</td>
                    <td><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</td></tr>`;
                });
                $("#movies-table tbody").append(trHTML);
            };

            // Update movie crawling status
            const update_movies = (id, message = '100%') => {
                let doneIcon = `<svg style="stroke-with:2px;stroke:seagreen;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="seagreen" class="bi bi-check-lg" viewBox="0 0 16 16">
                <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z"/>
                </svg>`;
                $("#" + id + " td:last-child").html(doneIcon + message);
            }

            //show alert with faded
            const alertBoxShow = (time = 3000) => {
                alertBox.fadeTo(time, 500).slideUp(500, function() {
                    alertBox.slideUp(500);
                });
            }

            //check valid url https format
            const isValidUrl = urlString => {
                var urlPattern = new RegExp('^(https?:\\/\\/)?' + // validate protocol
                    '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|' + // validate domain name
                    '((\\d{1,3}\\.){3}\\d{1,3}))' + // validate OR ip (v4) address
                    '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*' + // validate port and path
                    '(\\?[;&a-z\\d%_.~+=-]*)?' + // validate query string
                    '(\\#[-a-z\\d_]*)?$', 'i'); // validate fragment locator
                return !!urlPattern.test(urlString);
            }
        })
    </script>
    @include('admin.copyright')
    </div>
@endsection
