<x-app-layout title="{{ $title }} : Mail List">
    <x-slot name="script">
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                let cPage = 1;
                let box = "{{ $box }}";

                window.LoadData = function(customRoute, routeSufix) {
                    let route = APP_URL + "/list/" + box + "/data";
                    if (customRoute) {
                        route = customRoute;
                    }
                    if (routeSufix) {
                        route += routeSufix;
                    }
                    let dataWrap = document.querySelector("#datalist");
                    let databulder = new window.MailList(dataWrap, {
                        apiRoute: route,
                        box: box,
                        currentPage: cPage,
                        model: 'MailList',

                        startProcess: function() {
                            window.DataPath = route;
                            inProcess();
                        },
                        endProcess: function() {
                            new popup();
                            completaProcess();
                            mailDetailsEvent();
                        }
                    });
                    cPage = databulder.currentPage;
                }
                LoadData();
            });
        </script>
    </x-slot>
    <div class="listWrap">
        <div class="mail-list" id="datalist"></div>
    </div>

</x-app-layout>
