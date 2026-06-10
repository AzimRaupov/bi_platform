<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div>
    <div class="modal modal-blur fade" id="modal-report" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form action="{{route('company.chat.store')}}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Data to Dashboard</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="file" class="form-control" name="file"/>
                        </div>
                        <div class="col-lg-12">
                            <div>
                                <label class="form-label">Prompt</label>
                                <textarea class="form-control" rows="3" name="message"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="#" class="btn btn-link link-secondary btn-3" data-bs-dismiss="modal"> Cancel </a>
                        <button type="submit" class="btn btn-primary btn-5 ms-auto">
                            <!-- Download SVG icon from http://tabler.io/icons/icon/plus -->
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                                focusable="false"
                                class="icon icon-2"
                            >
                                <path d="M12 5l0 14" />
                                <path d="M5 12l14 0" />
                            </svg>
                            Create dashboard
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
