<!-- booking modal -->
<div class="booking-modal" id="bookingModal">
    <div class="booking-modal-backdrop"></div>

    <div class="booking-modal-content">
        <button
            class="booking-modal-close closeBookingModal"
            id="closeBookingModal"
            type="button"
        >
            &times;
        </button>

        <section class="booking-section">
            <div class="booking-layout">
                
                <div class="booking-card booking-summary-card">
                    <div class="booking-movie">
                        <div class="booking-poster">
                            <img
                                id="bookingPoster"
                                src=""
                                alt=""
                                width="238"
                            >
                        </div>

                        <div class="booking-movie-details">
                            <span class="movie-badge" id="bookingRating"></span>
                            <h2 id="bookingModalTitle"></h2>
                            <p class="booking-meta" id="bookingMeta"></p>
                            <p class="booking-description" id="bookingDescription"></p>
                        </div>
                    </div>

                    <div class="booking-info-grid">
                    <div class="booking-info-box">
                        <label for="bookingDate">Date</label>
                        <select id="bookingDate" class="booking-select"></select>
                    </div>

                    <div class="booking-info-box">
                        <label for="bookingCinema">Cinema</label>
                        <select id="bookingCinema" class="booking-select"></select>
                    </div>

                    <div class="booking-info-box">
                        <label for="bookingShowtime">Showtime</label>
                        <select id="bookingShowtime" class="booking-select"></select>
                    </div>
                    </div>

                    <input type="hidden" id="bookingShowtimeId" name="showtime_id">
                    <input type="hidden" id="bookingAdultPrice">
                    <input type="hidden" id="bookingChildPrice">

                    
                </div>

                <div class="booking-card booking-form-card">
                    <div class="booking-form-header">
                        <h2>Complete Your Booking</h2>
                        <p>Select ticket quantities and review your total before checkout.</p>
                    </div>

                    <form class="booking-form" id="bookingForm">
                        <input type="hidden" id="bookingMovieId" name="movie_id">
                        <input type="hidden" id="bookingShowtimeId" name="showtime_id">
                        <input type="hidden" id="bookingAdultPrice" name="adult_price">
                        <input type="hidden" id="bookingChildPrice" name="child_price">

                        <div class="booking-field-group">
                        <label for="fullName">Full Name</label>
                        <input
                            type="text"
                            id="fullName"
                            name="full_name"
                            value="<?= $isLoggedIn ? htmlspecialchars(trim(($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? ''))) : '' ?>"
                            readonly
                        >
                        </div>

                        <div class="booking-field-group">
                            <label for="email">Email Address</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="<?= $isLoggedIn ? htmlspecialchars($currentUser['email'] ?? '') : '' ?>"
                                readonly
                            >
                        </div>

                        <div class="booking-field-group">
                            <label for="phone">Phone Number</label>
                            <input
                                type="tel"
                                id="phone"
                                name="phone"
                                value="<?= $isLoggedIn ? htmlspecialchars($currentUser['phone'] ?? '') : '' ?>"
                                readonly
                            >
                        </div>

                        <div class="booking-ticket-grid">
                        <div class="ticket-counter">
                            <div class="ticket-counter-text">
                                <span>Adult Tickets</span>
                                <small id="adultPriceLabel"></small>
                            </div>

                            <div class="ticket-counter-controls">
                                <button type="button" class="qty-btn" data-target="adultQty" data-action="decrease">-</button>
                                <input type="number" id="adultQty" name="adult_quantity" min="0" value="1">
                                <button type="button" class="qty-btn" data-target="adultQty" data-action="increase">+</button>
                            </div>
                        </div>

                        <div class="ticket-counter">
                            <div class="ticket-counter-text">
                                <span>Child Tickets</span>
                                <small id="childPriceLabel"></small>
                            </div>

                            <div class="ticket-counter-controls">
                                <button type="button" class="qty-btn" data-target="childQty" data-action="decrease">-</button>
                                <input type="number" id="childQty" name="child_quantity" min="0" value="0">
                                <button type="button" class="qty-btn" data-target="childQty" data-action="increase">+</button>
                            </div>
                        </div>
                    </div>

                        <div class="booking-field-group">
                            <label for="notes">Special Notes</label>
                            <textarea id="notes" name="notes" rows="4" placeholder="Optional notes or requests"></textarea>
                        </div>

                        <div class="booking-total-box">
                            <div class="booking-total-row">
                                <span id="adultTotalText"></span>
                                <span id="adultTotalAmount"></span>
                            </div>

                            <div class="booking-total-row">
                                <span id="childTotalText"></span>
                                <span id="childTotalAmount"></span>
                            </div>

                            <div class="booking-total-row total">
                                <span>Total</span>
                                <span id="grandTotal"></span>
                            </div>
                        </div>

                        <div class="booking-actions">
                            <button type="button" class="btn btn-tertiary closeBookingModal" id="closeBookingAction">
                                Back
                            </button>
                            <button type="submit" class="btn btn-secondary">
                                Proceed to Checkout
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </section>
    </div>

</div>

<!-- trailer modal -->
<div class="trailer-modal" id="trailerModal">
    <div class="trailer-modal-backdrop"></div>

    <div class="trailer-modal-content">
    <button class="trailer-modal-close" id="closeTrailerModal" title="Close trailer">
        ×
    </button>

    <h2 class="trailer-modal-title" id="trailerModalTitle"></h2>

    <div class="trailer-modal-video-wrap">
        <iframe id="trailerFrame" src="" title="" 
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
        allowfullscreen="">
        </iframe>
    </div>
    </div>
    
</div>

<!-- booking success modal -->
<div class="booking-success-modal" id="bookingSuccessModal">
    <div class="booking-success-modal-backdrop"></div>

    <div class="booking-success-modal-content">
        <button
        class="booking-success-modal-close closeBookingSuccessModal"
        type="button"
        >
        &times;
        </button>

        <div class="booking-success-card">
        <span class="booking-success-badge">Booking Confirmed</span>

        <div class="booking-form-header">
            <h2>Booking Successful</h2>
            <p class="booking-success-copy">
            Your ticket has been booked successfully. A copy of your ticket information will be sent to
            <strong id="successEmail"></strong>.
            </p>
        </div>

        <div class="booking-success-details">
            <div class="booking-success-row">
            <span>Reference</span>
            <strong id="successReference"></strong>
            </div>

            <div class="booking-success-row">
            <span>Movie</span>
            <strong id="successMovieTitle"></strong>
            </div>

            <div class="booking-success-row">
            <span>Date</span>
            <strong id="successDate"></strong>
            </div>

            <div class="booking-success-row">
            <span>Cinema</span>
            <strong id="successCinema"></strong>
            </div>

            <div class="booking-success-row">
            <span>Showtime</span>
            <strong id="successShowtime"></strong>
            </div>

            <div class="booking-success-row">
            <span>Tickets</span>
            <strong id="successTickets"></strong>
            </div>

            <div class="booking-success-row">
            <span>Total</span>
            <strong id="successTotal"></strong>
            </div>
        </div>

        <div class="booking-success-actions">
            <button type="button" class="btn btn-secondary closeBookingSuccessModal">
            Done
            </button>
        </div>
        </div>
    </div>
</div>