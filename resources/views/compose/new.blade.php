<x-app-layout title="Compose New">
    <x-slot name="script">
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                $(function() {
                    $(".ccbcss-trig span").on("click", function() {
                        $("." + $(this).attr('data-target')).slideToggle('fast');
                        $(this).toggleClass('open');
                    });
                });

                var timeoutId
                window.editor.model.document.on('change:data', () => {
                    clearTimeout(timeoutId);
                    timeoutId = setTimeout(function() {
                        save_data('draft');
                    }, 2000);
                });

                window.save_data = function(status, _this) {
                    let editorData = editor.getData();
                    let autoSave = $(".autosave");
                    let idField = $("#idField");

                    let formData = $("#composeMail").serialize();
                    if (_this) {
                        _this.innerHTML = '<span class="working"></span>';
                    }
                    let data = {
                        'status': status,
                        'body': editorData,
                        'formData': formData,
                        'id': idField.val(),
                    };
                    autoSave.removeClass('saved').addClass('working');
                    axios.post('/compose', data).then((response) => {
                        response = response.data;
                        if (response.error) {
                            ntf(response.message, 'error');
                        } else {
                            //Set ID to
                            idField.val(response.id);
                            autoSave.removeClass('working').addClass('saved');
                            if (!response.auto_save) {
                                ntf(response.message, 'success');
                                document.getElementById("composeMail").reset();
                                $(".singleAddress").remove();
                                editor.setData('');
                                _this.innerHTML = 'Send';
                                clearTimeout(timeoutId);
                                $(".attachments").html('');
                                totalAttachmentSize = 0;
                                $("#attach_size").html("0 KB");
                                location.href = APP_URL;
                            }
                        }
                    }).catch((error) => {
                        ntf(error, 'error');
                    })
                }
                //Prevent Submit with press Enter
                $(document).ready(function() {
                    $(window).keydown(function(event) {
                        if (event.keyCode == 13) {
                            //event.preventDefault();
                            //return false;
                        }
                    });
                });

                window.findContact = function(e, _this, field) {
                    //See notes about 'which' and 'key'
                    //var toContacts = $("#toContacts");
                    var toContacts = $("#" + field + "Contacts");
                    if (e.keyCode == 13) {
                        var cont = $(_this).val();
                        var eml = "<div class='singleAddress'><input type='hidden' value='" + cont + "' name='" +
                            field + "[]'>" +
                            cont + " <span onclick='removeTo(this,\"" + field +
                            "\")' class='removeTo'>×</span></div>";

                        if (validateEmail(cont)) {
                            toContacts.append(eml);
                            $(_this).css('left', toContacts.width());
                            $(_this).val("").attr('placeholder', 'Add More');
                        }
                    }
                }

                window.addToCh = function(_this, field) {
                    var toContacts = $("#" + field + "Contacts");
                    var cont = $(_this).val();
                    if (validateEmail(cont)) {
                        var eml = "<div class='singleAddress'><input type='hidden' value='" + cont + "' name='" +
                            field + "[]'>" +
                            cont + " <span onclick='removeTo(this,\"" + field +
                            "\")' class='removeTo'>×</span></div>";
                        toContacts.append(eml);
                        $(_this).css('left', toContacts.width());
                        $(_this).val("").attr('placeholder', 'Add More');
                    } else {
                        ntf("Email address not valid", "error");
                    }
                }

                window.validateEmail = function(email) {
                    var re =
                        /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                    return re.test(String(email).toLowerCase());
                }

                window.removeTo = function(_this, field) {
                    var toContacts = $("#" + field + "Contacts");
                    $(_this).parent().remove();
                    $(toContacts).parent().find('.contectIn').css('left', toContacts.width());
                }

                let totalAttachmentSize = 0;

                window.uploadFile = function(_this) {
                    let file = _this;
                    let upwrap = document.querySelector(".attachments");
                    let fileUp = new FileUploader({
                        route: '{{ route('attachment.upload') }}',
                        items: upwrap,
                        name: 'mail_attachment',
                        onProcess: (obj, p) => {
                            //console.log(obj.file);
                            totalAttachmentSize += obj.file.size;
                            document.getElementById("attach_size").innerHTML = obj.humanFileSize(
                                totalAttachmentSize);
                        },
                        onComplete: (obj) => {
                            console.log('upload complete');
                        },
                        callback: function(res, obj) {
                            if (res.data.error) {
                                //console.log('done uploading')
                                ntf(res.data.msg, 'error');
                            } else {
                                let inputFileName = document.createElement('input');
                                inputFileName.name = 'attachments[]';
                                inputFileName.type = 'hidden';
                                inputFileName.value = res.data.fileName;
                                obj.item.appendChild(inputFileName);
                            }
                        },
                        FormData: {
                            'upload-type': 'attachment',
                        },
                    });
                    let files = file.files;
                    fileUp.upload(files[0]);
                }

            });
        </script>
    </x-slot>

    <form id="composeMail">
        <div class="compose-new">
            <input type="hidden" id="idField" value="{{ $data->id }}">
            <div class="compose-header">
                <div class="recipients-group">
                    <div class="ccbcss-trig">
                        <span class="{{ count($data->addresses('cc')) > 0 ? 'open' : '' }}"
                            data-target="ccInput">Cc</span>
                        <span class="{{ count($data->addresses('bcc')) > 0 ? 'open' : '' }}"
                            data-target="bccInput">Bcc</span>
                    </div>
                    <div class="contactArea">
                        <div class="toArea compose-input">
                            <div id="toContacts">
                                {{-- To Address Here --}}
                                @foreach ($data->addresses() as $to)
                                    <div class="singleAddress">
                                        <input type="hidden" value="{{ $to->address }}" name="to[]">
                                        {{ $to->address }}
                                        <span onclick="removeTo(this,&quot;to&quot;)" class="removeTo">×</span>
                                    </div>
                                @endforeach
                            </div>
                            <input class="contectIn" id="addToCon" onkeyup="findContact(event, this, 'to')"
                                onchange="addToCh(this, 'to')" type="text" placeholder="Recipients">
                        </div>
                        <div class="ccInput collapse {{ count($data->addresses('cc')) > 0 ? 'has-address' : '' }} ">
                            <div id="ccPrt" class="toArea compose-input">
                                <div id="ccContacts">
                                    {{-- CC Address Here --}}
                                    @foreach ($data->addresses('cc') as $to)
                                        <div class="singleAddress">
                                            <input type="hidden" value="{{ $to->address }}" name="cc[]">
                                            {{ $to->address }}
                                            <span onclick="removeTo(this,&quot;to&quot;)" class="removeTo">×</span>
                                        </div>
                                    @endforeach
                                </div>
                                <input class="contectIn" id="addCcCon" onkeyup="findContact(event, this, 'cc')"
                                    onchange="addToCh(this, 'cc')" type="text" placeholder="Cc">
                            </div>
                        </div>
                        <div class="bccInput collapse  {{ count($data->addresses('bcc')) > 0 ? 'has-address' : '' }}">
                            <div id="bccPrt" class="toArea compose-input">
                                <div id="bccContacts">
                                    {{-- BCC Address Here --}}
                                    @foreach ($data->addresses('bcc') as $to)
                                        <div class="singleAddress">
                                            <input type="hidden" value="{{ $to->address }}" name="bcc[]">
                                            {{ $to->address }}
                                            <span onclick="removeTo(this,&quot;to&quot;)" class="removeTo">×</span>
                                        </div>
                                    @endforeach
                                </div>
                                <input class="contectIn" id="addBccCon" onkeyup="findContact(event, this, 'bcc')"
                                    onchange="addToCh(this, 'bcc')" type="text" placeholder="Bcc">
                            </div>
                        </div>
                    </div>
                </div>
                <input type="text" class="subject compose-input" value="{{ $data->subject }}" name="subject"
                    placeholder="Subject">
            </div>
            <div class="compose-body">
                <div id="editor">{!! isset($data->MailDetails->msg_body) ? $data->MailDetails->msg_body : '' !!}</div>
            </div>
            <div class="compose-head">
                <div class="compose-footer">
                    <button class="btn btn-primary" type="button" onclick="save_data('active',this)">Send</button>
                    <span class="autosave"></span>
                    <div class="attachment-area">
                        <div class="attachments"></div>
                        <input class="collapse" onchange="uploadFile(this)" type="file" id="attachment-select">
                        <label for="attachment-select" class="tooltip" data-position='top' data-bg='#555'
                            title="Attach Something">{{ getIcon('attach') }}</label>
                    </div>
                    <div class="attach-from-gdrive">
                        <label for="attachment-gdrive" class="attach-gdrive tooltip" data-position='top' data-bg='#555'
                            title="Attach From Google Drive"><svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
                                width="32" height="32" viewBox="0 0 32 32">
                                <path
                                    d="M 11.4375 5 L 11.15625 5.46875 L 3.15625 18.46875 L 2.84375 18.96875 L 3.125 19.5 L 7.125 26.5 L 7.40625 27 L 24.59375 27 L 24.875 26.5 L 28.875 19.5 L 29.15625 18.96875 L 28.84375 18.46875 L 20.84375 5.46875 L 20.5625 5 Z M 13.78125 7 L 19.4375 7 L 26.21875 18 L 20.5625 18 Z M 12 7.90625 L 14.96875 12.75 L 8.03125 24.03125 L 5.15625 19 Z M 16.15625 14.65625 L 18.21875 18 L 14.09375 18 Z M 12.875 20 L 26.28125 20 L 23.40625 25 L 9.78125 25 Z">
                                </path>
                            </svg>
                        </label>
                    </div>
                    <div id='attach_size'>0 KB</div>
                </div>
            </div>
        </div>
    </form>
</x-app-layout>
