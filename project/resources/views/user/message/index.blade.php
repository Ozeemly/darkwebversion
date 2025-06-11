@extends('layouts.front')
@section('css')
    <link rel="stylesheet" href="{{ asset('assets/front/css/datatables.css') }}">
    <style>
        .chat-list-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .chat-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .chat-list-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background-color 0.2s ease;
            position: relative;
        }
        
        .chat-list-item:hover {
            background-color: #f9f9f9;
        }
        
        .chat-list-item.unread {
            background-color: #f0f8ff;
        }
        
        .chat-user-avatar {
            flex: 0 0 48px;
            height: 48px;
            margin-right: 15px;
            position: relative;
        }
        
        .chat-user-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .online-indicator {
            width: 12px;
            height: 12px;
            background-color: #4CAF50;
            border-radius: 50%;
            position: absolute;
            bottom: 0;
            right: 0;
            border: 2px solid white;
        }
        
        .chat-content {
            flex: 1;
            min-width: 0;
        }
        
        .chat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4px;
        }
        
        .chat-user-name {
            font-weight: 600;
            font-size: 16px;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .chat-time {
            font-size: 12px;
            color: #888;
            white-space: nowrap;
        }
        
        .chat-preview {
            font-size: 14px;
            color: #666;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin: 0;
        }
        
        .chat-subject {
            font-size: 13px;
            color: #444;
            font-weight: 500;
            margin-top: 2px;
        }
        
        .compose-btn {
            position: fixed;
            right: 25px;
            bottom: 25px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #3498db;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 100;
            border: none;
            font-size: 24px;
        }
        
        .compose-btn:hover {
            background: #2980b9;
        }
        
        .dropdown-menu {
            padding: 8px 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border: none;
            border-radius: 8px;
        }
        
        .dropdown-item {
            padding: 8px 16px;
            display: flex;
            align-items: center;
        }
        
        .dropdown-item i {
            margin-right: 8px;
            font-size: 16px;
            width: 20px;
            text-align: center;
        }
        
        .empty-state {
            padding: 40px 20px;
            text-align: center;
            color: #888;
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #ddd;
        }
        
        /* Context menu */
        .context-menu {
            display: none;
            position: absolute;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            z-index: 1000;
            overflow: hidden;
        }
        
        .context-menu-item {
            padding: 10px 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
            transition: background 0.2s;
        }
        
        .context-menu-item:hover {
            background: #f5f5f5;
        }
        
        .context-menu-item i {
            margin-right: 10px;
            width: 16px;
            text-align: center;
        }
        
        /* Mobile optimizations */
        @media (max-width: 768px) {
            .chat-list-item {
                padding: 12px 15px;
            }
            
            .chat-user-avatar {
                flex: 0 0 40px;
                height: 40px;
                margin-right: 12px;
            }
            
            .chat-user-name {
                font-size: 15px;
            }
            
            .chat-preview {
                font-size: 13px;
            }
            
            .compose-btn {
                width: 50px;
                height: 50px;
                right: 15px;
                bottom: 15px;
            }
        }
    </style>
@endsection
@section('content')
    <div class="gs-user-panel-review wow-replaced" data-wow-delay=".1s">
        <div class="container">
            <div class="d-flex">
                <!-- sidebar -->
                @include('includes.user.sidebar')
                <!-- main content -->
                <div class="gs-dashboard-user-content-wrapper gs-dashboard-outlet">
                    <!-- page title -->
                    <div class="ud-page-title-box d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <!-- mobile sidebar trigger btn -->
                        <h3 class="ud-page-title">@lang('Messages')</h3>
                        <button data-bs-toggle="modal" data-bs-target="#vendorform"
                            class="template-btn md-btn black-btn data-table-btn">
                            <i class="fas fa-plus"></i> @lang('Compose Message')</button>
                    </div>

                    <div class="chat-list-container">
                        <ul class="chat-list">
                            @forelse ($convs as $conv)
                                <li class="chat-list-item" data-id="{{ $conv->id }}" data-href="{{ route('user-message', $conv->id) }}">
                                    <div class="chat-user-avatar">
                                        @if ($user->id == $conv->sent->id)
                                            <img src="{{ $conv->recieved->photo != null ? ($conv->recieved->is_provider == 1 ? $conv->recieved->photo : asset('assets/images/users/' . $conv->recieved->photo)) : asset('assets/images/noimage.png') }}" 
                                                alt="{{ $conv->recieved->name }}" class="avatar-img">
                                        @else
                                            <img src="{{ $conv->sent->photo != null ? ($conv->sent->is_provider == 1 ? $conv->sent->photo : asset('assets/images/users/' . $conv->sent->photo)) : asset('assets/images/noimage.png') }}" 
                                                alt="{{ $conv->sent->name }}" class="avatar-img">
                                        @endif
                                    </div>
                                    <div class="chat-content">
                                        <div class="chat-header">
                                            @if ($user->id == $conv->sent->id)
                                                <h4 class="chat-user-name">{{ $conv->recieved->name }}</h4>
                                            @else
                                                <h4 class="chat-user-name">{{ $conv->sent->name }}</h4>
                                            @endif
                                            <span class="chat-time">{{ $conv->created_at->diffForHumans() }}</span>
                                        </div>
                                        <div class="chat-subject">{{ $conv->subject }}</div>
                                    </div>
                                </li>
                            @empty
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <p>{{ __('No Messages Found') }}</p>
                                </div>
                            @endforelse
                        </ul>
                    </div>
                    
                    <div class="mt-4">
                        {{ $convs->links('includes.frontend.pagination') }}
                    </div>
                    
                    <!-- Context Menu (Hidden by default) -->
                    <div class="context-menu" id="contextMenu">
                        <div class="context-menu-item view-message">
                            <i class="fas fa-eye"></i> View Message
                        </div>
                        <div class="context-menu-item pin-message">
                            <i class="fas fa-thumbtack"></i> Pin to Top
                        </div>
                        <div class="context-menu-item delete-message">
                            <i class="fas fa-trash"></i> Delete
                        </div>
                    </div>
                    
                    <!-- Mobile floating action button -->
                    <button class="compose-btn d-md-none" data-bs-toggle="modal" data-bs-target="#vendorform">
                        <i class="fas fa-pen"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- user dashboard wrapper end -->

    <!-- Compose Message Modal -->
    <div class="modal gs-modal fade" id="vendorform" tabindex="-1" aria-modal="true" role="dialog">
        <form action="{{ route('user-contact') }}" method="POST"
            class="modal-dialog assign-rider-modal-dialog modal-dialog-centered emailreply">
            {{ csrf_field() }}
            <div class="modal-content assign-rider-modal-content form-group">
                <div class="modal-header w-100">
                    <h4 class="title">@lang('Send Message')</h4>
                    <button type="button" data-bs-dismiss="modal">
                        <i class="fa-regular fa-circle-xmark gs-modal-close-btn"></i>
                    </button>
                </div>
                <!-- modal body start  -->
                <div class="input-label-wrapper w-100">
                    <input type="hidden" name="vendor_id" value="{{ auth()->id() }}">
                    <input type="text" class="form-control border px-3 mb-4" name="email"
                        placeholder="@lang('Enter Email')" required="">

                    <input type="text" class="form-control border px-3 mb-4" name="subject"
                        placeholder="@lang('Subject')" required="">

                    <textarea class="form-control border px-3 mb-4" name="message" placeholder="{{ __('Your Message') }}" required=""></textarea>

                    <input type="hidden" name="name" value="{{ Auth::user()->name }}">
                    <input type="hidden" name="user_id" value="{{ Auth::user()->id }}">
                </div>
                <!-- Assign Rider Button  -->
                <button class="template-btn" type="submit">@lang('Send Message')</button>
                <!-- modal body end  -->
            </div>
        </form>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal gs-modal fade" id="confirm-delete" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog confirm-delete-modal-dialog modal-dialog-centered">
            <div class="modal-content confirm-delete-modal-content form-group">
                <div class="modal-header delete-modal-header w-100">
                    <div class="title-des-wrapper">
                        <h4 class="title">@lang('Confirm Delete ?')</h4>
                        <h5 class="sub-title">{{ __('Do you want to proceed?') }}</h5>
                    </div>
                </div>
                <div class="row row-cols-2 w-100">
                    <div class="col">
                        <a class="template-btn black-btn w-100 btn-ok" href=""
                            type="button">@lang('Delete')</a>
                    </div>
                    <div class="col">
                        <button class="template-btn w-100" data-bs-dismiss="modal"
                            type="button">@lang('Cancel')</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        (function($) {
            "use strict";
            
            // Handle clicking on chat item
            $('.chat-list-item').on('click', function() {
                window.location.href = $(this).data('href');
            });
            
            // Context menu handling
            let activeItem = null;
            
            // For desktop - right click
            $('.chat-list-item').on('contextmenu', function(e) {
                e.preventDefault();
                
                // Store the item that was right-clicked
                activeItem = $(this);
                
                // Position and show context menu
                const contextMenu = $('#contextMenu');
                contextMenu.css({
                    left: e.pageX + 'px',
                    top: e.pageY + 'px',
                    display: 'block'
                });
            });
            
            // For mobile - long press
            let longPressTimer;
            $('.chat-list-item').on('touchstart', function(e) {
                const touchedItem = $(this);
                longPressTimer = setTimeout(function() {
                    activeItem = touchedItem;
                    
                    // Get touch position
                    const touch = e.originalEvent.touches[0];
                    
                    // Position and show context menu
                    const contextMenu = $('#contextMenu');
                    contextMenu.css({
                        left: touch.pageX + 'px',
                        top: touch.pageY + 'px',
                        display: 'block'
                    });
                }, 500); // 500ms long press
            });
            
            $('.chat-list-item').on('touchend touchmove', function() {
                clearTimeout(longPressTimer);
            });
            
            // Hide context menu when clicking elsewhere
            $(document).on('click touchstart', function() {
                $('#contextMenu').hide();
            });
            
            // Handle context menu actions
            $('.view-message').on('click', function() {
                if (activeItem) {
                    window.location.href = activeItem.data('href');
                }
            });
            
            $('.delete-message').on('click', function() {
                if (activeItem) {
                    const itemId = activeItem.data('id');
                    const deleteUrl = "{{ route('user-message-delete', ':id') }}".replace(':id', itemId);
                    $('#confirm-delete').find('.btn-ok').attr('href', deleteUrl);
                    $('#confirm-delete').modal('show');
                }
            });
            
            $('.pin-message').on('click', function() {
                if (activeItem) {
                    // Move the item to the top of the list
                    const parent = activeItem.parent();
                    activeItem.detach().prependTo(parent);
                    
                    // You would typically add an AJAX call here to save the pinned status
                    // For now, we're just moving the element in the DOM
                }
            });
            
            // Confirm delete modal setup
            $('#confirm-delete').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });
        })(jQuery);
    </script>
@endsection