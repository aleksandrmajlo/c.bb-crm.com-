<template>
    <Teleport to="body">
        <div class="order-edit-backdrop" @click.self="close" role="presentation">
            <section
                ref="dialog"
                class="order-edit-modal"
                role="dialog"
                aria-modal="true"
                :aria-labelledby="`order-edit-title-${order.booking_id}`"
            >
                <header class="order-edit-modal__header">
                    <div>
                        <p class="order-edit-modal__eyebrow">{{ $t('your_order') }}</p>
                        <h2 :id="`order-edit-title-${order.booking_id}`">{{ $t('edit_booking') }}</h2>
                        <p class="order-edit-modal__fixed">
                            {{ order.table_title }} · {{ order.date_title || order.date }} · {{ draft.time.text }}
                        </p>
                    </div>
                    <button
                        type="button"
                        class="order-edit-modal__close"
                        :aria-label="$t('close')"
                        :disabled="isSaving"
                        @click="close"
                    >×</button>
                </header>

                <div class="order-edit-modal__body">
                    <div class="edit-field">
                        <label :for="`employment-search-${order.booking_id}`">{{ $t('search_session') }}</label>
                        <input
                            ref="searchInput"
                            :id="`employment-search-${order.booking_id}`"
                            v-model.trim="employmentSearch"
                            type="search"
                            class="edit-control"
                            :placeholder="$t('search_session_placeholder')"
                        >
                    </div>

                    <div class="edit-field">
                        <label :for="`employment-edit-${order.booking_id}`">{{ $t('select_emp') }}</label>
                        <div class="edit-select">
                            <select
                                :id="`employment-edit-${order.booking_id}`"
                                v-model="selectedEmploymentId"
                                class="edit-control"
                                @change="employmentChanged"
                            >
                                <option
                                    v-for="employment in filteredEmployments"
                                    :key="employment.id"
                                    :value="String(employment.id)"
                                >{{ employment.title }}</option>
                            </select>
                        </div>
                    </div>

                    <div v-if="priceChoices.length" class="edit-field">
                        <label :for="`duration-edit-${order.booking_id}`">{{ $t('duration_price') }}</label>
                        <div class="edit-select">
                            <select
                                :id="`duration-edit-${order.booking_id}`"
                                v-model="employmentPriceId"
                                class="edit-control"
                                @change="selectionChanged"
                            >
                                <option
                                    v-for="choice in priceChoices"
                                    :key="choice.id"
                                    :value="String(choice.id)"
                                >{{ priceChoiceLabel(choice) }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="edit-field">
                        <label :for="`trainer-edit-${order.booking_id}`">{{ $t('trainer') }}</label>
                        <div class="edit-select">
                            <select
                                :id="`trainer-edit-${order.booking_id}`"
                                v-model="selectedTrainerId"
                                class="edit-control"
                                @change="selectionChanged"
                            >
                                <option value="">{{ $t('without_trainer') }}</option>
                                <option
                                    v-for="trainer in trainers"
                                    :key="trainer.id"
                                    :value="String(trainer.id)"
                                >{{ trainerLabel(trainer) }}</option>
                            </select>
                        </div>
                    </div>

                    <fieldset v-if="Object.keys(optionDefinitions).length" class="edit-options">
                        <legend>{{ $t('ten_opt') }}</legend>
                        <div
                            v-for="(option, key) in optionDefinitions"
                            :key="key"
                            class="edit-option"
                        >
                            <label class="edit-option__check">
                                <input
                                    v-model="selectedOptionKeys"
                                    type="checkbox"
                                    class="edit-option__input"
                                    :value="String(key)"
                                    @change="optionChanged(key)"
                                >
                                <span>{{ option.title }} — {{ money(option.price) }} грн</span>
                            </label>
                            <div v-if="option.isPerPiece && isOptionSelected(key)" class="edit-quantity">
                                <button
                                    type="button"
                                    :disabled="quantity(key) <= 1"
                                    :aria-label="$t('decrease_quantity')"
                                    @click="changeQuantity(key, -1)"
                                >−</button>
                                <input
                                    :value="quantity(key)"
                                    type="number"
                                    class="edit-quantity__input"
                                    min="1"
                                    max="99"
                                    inputmode="numeric"
                                    :aria-label="$t('quantity')"
                                    @input="setQuantity(key, $event.target.value)"
                                    @blur="normalizeQuantity(key)"
                                >
                                <button
                                    type="button"
                                    :disabled="quantity(key) >= 99"
                                    :aria-label="$t('increase_quantity')"
                                    @click="changeQuantity(key, 1)"
                                >+</button>
                            </div>
                        </div>
                    </fieldset>

                    <div class="edit-price" aria-live="polite">
                        <div>
                            <span>{{ $t('new_total') }}</span>
                            <strong v-if="preview">{{ money(preview.total) }} грн</strong>
                            <strong v-else>…</strong>
                        </div>
                        <p>{{ $t('server_recalculates_price') }}</p>
                    </div>

                    <p v-if="errorMessage" class="order-edit-modal__error" role="alert">
                        {{ errorMessage }}
                    </p>
                </div>

                <footer class="order-edit-modal__footer">
                    <button type="button" class="edit-button edit-button--secondary" :disabled="isSaving" @click="close">
                        {{ $t('cancel') }}
                    </button>
                    <button
                        type="button"
                        class="edit-button edit-button--primary"
                        :disabled="isSaving || !selectedEmploymentId"
                        @click="save"
                    >
                        {{ isSaving ? $t('saving') : $t('save_changes') }}
                    </button>
                </footer>
            </section>
        </div>
    </Teleport>
</template>

<script>
import {mapState} from 'vuex';

export default {
    name: 'OrderEditModal',
    props: {
        order: {
            type: Object,
            required: true,
        },
    },
    emits: ['close', 'saved'],
    data() {
        return {
            draft: {
                ...this.order,
                time: {...this.order.time},
            },
            selectedEmploymentId: String(this.order.employment_id || ''),
            employmentPriceId: String(this.order.employment_price_id || 'default'),
            selectedTrainerId: this.order.trainer_id ? String(this.order.trainer_id) : '',
            selectedOptionKeys: [],
            optionQuantities: {},
            employmentSearch: '',
            preview: null,
            previewTimer: null,
            previewSequence: 0,
            isSaving: false,
            errorMessage: '',
            previousActiveElement: null,
        };
    },
    computed: {
        ...mapState({
            employments: state => state.employments,
            tables: state => state.tables,
            trainers: state => state.trainers,
            tennisOptionsCatalog: state => state.tennis_options_catalog,
        }),
        employmentList() {
            const all = Object.values(this.employments || {});
            const tableEmployments = this.tables?.[this.order.table_id]?.employments || [];
            const allowedIds = new Set(tableEmployments.map(employment => String(employment.id)));
            if (!allowedIds.size) {
                return all;
            }

            return all.filter(employment => (
                allowedIds.has(String(employment.id))
                || String(employment.id) === String(this.order.employment_id)
            ));
        },
        filteredEmployments() {
            const query = this.employmentSearch.toLocaleLowerCase();
            if (!query) {
                return this.employmentList;
            }

            // Always keep the selected item visible while the user searches.
            return this.employmentList.filter(employment => (
                String(employment.id) === String(this.selectedEmploymentId)
                || String(employment.title || '').toLocaleLowerCase().includes(query)
            ));
        },
        selectedEmployment() {
            return this.employments?.[this.selectedEmploymentId] || null;
        },
        priceChoices() {
            return this.selectedEmployment?.price_choices || [];
        },
        optionDefinitions() {
            if (
                this.selectedEmployment
                && Object.prototype.hasOwnProperty.call(this.selectedEmployment, 'tennis_options')
            ) {
                return this.selectedEmployment.tennis_options || {};
            }

            // Compatibility with an older API response that only contained a
            // global option catalog and no per-employment option collection.
            return this.tennisOptionsCatalog || {};
        },
        selectedPrice() {
            return this.priceChoices.find(
                choice => String(choice.id) === String(this.employmentPriceId)
            ) || this.priceChoices[0] || null;
        },
    },
    mounted() {
        this.restoreOptions();
        this.previousActiveElement = document.activeElement;
        document.addEventListener('keydown', this.keydown);
        document.body.classList.add('order-edit-open');
        this.schedulePreview();
        this.$nextTick(() => this.$refs.searchInput?.focus());
    },
    beforeUnmount() {
        window.clearTimeout(this.previewTimer);
        document.removeEventListener('keydown', this.keydown);
        document.body.classList.remove('order-edit-open');
        this.previousActiveElement?.focus?.();
    },
    methods: {
        money(value) {
            return Number(value || 0).toLocaleString('uk-UA', {maximumFractionDigits: 2});
        },
        priceChoiceLabel(choice) {
            if (!choice?.minutes) {
                return this.$t('base_hourly_price', {price: choice?.price || 0});
            }

            return this.$t('duration_price_option', {
                minutes: choice.minutes,
                price: choice.price,
            });
        },
        trainerPrice(trainer) {
            return Number(trainer.tariffs?.[this.selectedEmploymentId] ?? 0);
        },
        trainerLabel(trainer) {
            return `${trainer.name} · +${this.money(this.trainerPrice(trainer))} грн/год`;
        },
        employmentChanged() {
            this.employmentPriceId = 'default';
            this.selectedTrainerId = '';
            this.selectedOptionKeys = [];
            this.optionQuantities = {};
            this.selectionChanged();
        },
        selectionChanged() {
            this.applyDuration();
            this.errorMessage = '';
            this.schedulePreview();
        },
        applyDuration() {
            const startMinutes = this.timeToMinutes(this.order.time.start);
            const end = this.selectedPrice?.minutes
                ? (startMinutes + Number(this.selectedPrice.minutes)) / 60
                : (this.order.slot_end ?? this.order.time.end);
            this.draft.time = {
                ...this.draft.time,
                end,
                text: `${this.formatTime(this.order.time.start)} - ${this.formatTime(end)}`,
            };
        },
        timeToMinutes(value) {
            if (typeof value === 'string' && value.includes(':')) {
                const [hours, minutes = 0] = value.split(':').map(Number);
                return (hours * 60) + minutes;
            }
            return Math.round(Number(value || 0) * 60);
        },
        formatTime(value) {
            const total = this.timeToMinutes(value);
            return `${String(Math.floor(total / 60)).padStart(2, '0')}:${String(total % 60).padStart(2, '0')}`;
        },
        restoreOptions() {
            const options = this.order.tennis_options || {};
            if (Array.isArray(options)) {
                options.forEach(key => {
                    const normalizedKey = String(key);
                    if (!this.selectedOptionKeys.includes(normalizedKey)) {
                        this.selectedOptionKeys.push(normalizedKey);
                    }
                    this.optionQuantities[normalizedKey] = (this.optionQuantities[normalizedKey] || 0) + 1;
                });
                return;
            }

            Object.entries(options).forEach(([key, value]) => {
                if (String(key).startsWith('_') || String(key).startsWith('customer_id_')) {
                    return;
                }
                this.selectedOptionKeys.push(String(key));
                this.optionQuantities[String(key)] = Number(value || 1);
            });
        },
        isOptionSelected(key) {
            return this.selectedOptionKeys.includes(String(key));
        },
        quantity(key) {
            const value = Number.parseInt(this.optionQuantities[String(key)], 10);
            return Math.min(99, Math.max(1, Number.isFinite(value) ? value : 1));
        },
        optionChanged(key) {
            if (this.isOptionSelected(key) && this.optionQuantities[String(key)] === undefined) {
                this.optionQuantities[String(key)] = 1;
            }
            this.selectionChanged();
        },
        setQuantity(key, value) {
            this.optionQuantities[String(key)] = value;
            if (value !== '') {
                this.schedulePreview();
            }
        },
        normalizeQuantity(key) {
            this.optionQuantities[String(key)] = this.quantity(key);
            this.schedulePreview();
        },
        changeQuantity(key, direction) {
            this.optionQuantities[String(key)] = Math.min(99, Math.max(1, this.quantity(key) + direction));
            this.schedulePreview();
        },
        selectedOptions() {
            const selected = {};
            this.selectedOptionKeys.forEach(key => {
                selected[key] = this.optionDefinitions[key]?.isPerPiece ? this.quantity(key) : 1;
            });
            return selected;
        },
        buildDraft() {
            const trainer = this.trainers.find(
                item => String(item.id) === String(this.selectedTrainerId)
            );
            return {
                ...this.draft,
                employment_id: this.selectedEmploymentId,
                employment_title: this.selectedEmployment?.title || '',
                employment_price_id: this.employmentPriceId,
                employment_price_label: this.selectedPrice ? this.priceChoiceLabel(this.selectedPrice) : '',
                trainer_id: trainer?.id || null,
                trainer_title: trainer?.name || '',
                trainer_price: trainer ? this.trainerPrice(trainer) : 0,
                tennis_options: this.selectedOptions(),
            };
        },
        schedulePreview() {
            window.clearTimeout(this.previewTimer);
            this.previewTimer = window.setTimeout(this.loadPreview, 250);
        },
        async loadPreview() {
            const sequence = ++this.previewSequence;
            try {
                const preview = await this.$store.dispatch('previewBookingEdit', this.buildDraft());
                if (sequence === this.previewSequence) {
                    this.preview = preview;
                }
            } catch (error) {
                if (sequence === this.previewSequence) {
                    this.preview = null;
                }
            }
        },
        firstError(error) {
            const errors = error?.response?.data?.errors || {};
            const first = Object.values(errors).flat()[0];
            return first || error?.response?.data?.message || this.$t('booking_update_failed');
        },
        async save() {
            if (this.isSaving || !this.selectedEmploymentId) {
                return;
            }
            this.isSaving = true;
            this.errorMessage = '';
            try {
                await this.$store.dispatch('updateBooking', this.buildDraft());
                this.$emit('saved');
                this.$emit('close');
            } catch (error) {
                this.errorMessage = this.firstError(error);
            } finally {
                this.isSaving = false;
            }
        },
        keydown(event) {
            if (event.key === 'Escape') {
                this.close();
                return;
            }
            if (event.key !== 'Tab') {
                return;
            }

            const focusable = Array.from(this.$refs.dialog?.querySelectorAll(
                'button:not([disabled]), input:not([disabled]), select:not([disabled]), [href]'
            ) || []).filter(element => element.offsetParent !== null);
            if (!focusable.length) {
                return;
            }

            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        },
        close() {
            if (!this.isSaving) {
                this.$emit('close');
            }
        },
    },
};
</script>

<style scoped>
:global(body.order-edit-open) {
    overflow: hidden;
}

.order-edit-backdrop {
    position: fixed;
    z-index: 10000;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: rgba(15, 23, 42, .62);
    backdrop-filter: blur(4px);
}

.order-edit-modal {
    width: min(680px, 100%);
    max-height: calc(100vh - 40px);
    overflow: auto;
    border-radius: 22px;
    background: #fff;
    color: #17221b;
    box-shadow: 0 24px 70px rgba(15, 23, 42, .28);
}

.order-edit-modal__header,
.order-edit-modal__footer {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 18px;
    padding: 22px 24px;
}

.order-edit-modal__header {
    position: sticky;
    z-index: 2;
    top: 0;
    border-bottom: 1px solid #e5e7eb;
    background: rgba(255, 255, 255, .97);
}

.order-edit-modal__header h2 {
    margin: 0;
    font-size: 24px;
}

.order-edit-modal__eyebrow,
.order-edit-modal__fixed {
    margin: 0 0 5px;
    color: #667085;
    font-size: 13px;
}

.order-edit-modal__eyebrow {
    color: #269943;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .05em;
}

.order-edit-modal__close {
    width: 38px;
    height: 38px;
    flex: 0 0 38px;
    border: 0;
    border-radius: 50%;
    background: #f1f5f9;
    color: #344054;
    font-size: 26px;
    line-height: 1;
    cursor: pointer;
}

.order-edit-modal__body {
    padding: 22px 24px;
}

.edit-field {
    margin-bottom: 17px;
}

.edit-field > label,
.edit-options legend {
    display: block;
    margin-bottom: 7px;
    color: #344054;
    font-size: 14px;
    font-weight: 750;
}

.edit-control {
    width: 100%;
    min-height: 48px;
    padding: 0 42px 0 14px;
    border: 1px solid #cbd5e1;
    border-radius: 12px;
    outline: none;
    background: #fff;
    color: #17221b;
    font-size: 15px;
}

input.edit-control {
    padding-right: 14px;
}

.edit-control:focus {
    border-color: #f58220;
    box-shadow: 0 0 0 4px rgba(245, 130, 32, .14);
}

.edit-select {
    position: relative;
}

.edit-select::after {
    position: absolute;
    top: 17px;
    right: 17px;
    width: 9px;
    height: 9px;
    border-right: 2px solid #667085;
    border-bottom: 2px solid #667085;
    pointer-events: none;
    content: '';
    transform: rotate(45deg);
}

.edit-select select {
    appearance: none;
}

.edit-options {
    margin: 6px 0 18px;
    padding: 0;
    border: 0;
}

.edit-option {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 12px 0;
    border-bottom: 1px solid #eef2f6;
}

.edit-option__check {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

.edit-option__check .edit-option__input {
    position: static;
    z-index: auto;
    display: inline-block;
    visibility: visible;
    width: 20px;
    height: 20px;
    margin: 0;
    padding: 0 !important;
    flex: 0 0 20px;
    opacity: 1;
    -webkit-appearance: checkbox;
    appearance: checkbox;
    accent-color: #269943;
}

.edit-option__check .edit-option__input:focus-visible {
    outline: 2px solid #f58220;
    outline-offset: 2px;
}

.edit-quantity {
    display: flex;
    overflow: hidden;
    flex: 0 0 auto;
    border: 1px solid #cbd5e1;
    border-radius: 9px;
}

.edit-quantity button,
.edit-quantity input {
    width: 38px;
    height: 36px;
    border: 0;
    background: #fff;
    text-align: center;
}

.edit-quantity button {
    background: #f8fafc;
    color: #146c2e;
    font-size: 20px;
    cursor: pointer;
}

.edit-quantity .edit-quantity__input {
    position: static;
    z-index: auto;
    display: block;
    visibility: visible;
    min-width: 0;
    margin: 0;
    padding: 0 4px !important;
    border-right: 1px solid #e5e7eb;
    border-left: 1px solid #e5e7eb;
    border-radius: 0;
    outline: none;
    background: #fff;
    color: #17221b !important;
    font-size: 16px;
    font-weight: 700;
    line-height: 36px;
    opacity: 1;
    box-shadow: none;
    -webkit-text-fill-color: #17221b;
    font-variant-numeric: tabular-nums;
    -moz-appearance: textfield;
    appearance: textfield;
}

.edit-quantity .edit-quantity__input::-webkit-inner-spin-button,
.edit-quantity .edit-quantity__input::-webkit-outer-spin-button {
    margin: 0;
    -webkit-appearance: none;
}

.edit-quantity .edit-quantity__input:focus-visible {
    position: relative;
    z-index: 1;
    outline: 2px solid #f58220;
    outline-offset: -2px;
}

.edit-price {
    padding: 16px;
    border: 1px solid #b7e1c2;
    border-radius: 14px;
    background: #f2fbf4;
}

.edit-price div {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    color: #146c2e;
    font-size: 18px;
}

.edit-price p {
    margin: 5px 0 0;
    color: #667085;
    font-size: 12px;
}

.order-edit-modal__error {
    margin: 14px 0 0;
    padding: 12px 14px;
    border-radius: 10px;
    background: #fff1f2;
    color: #b42318;
    font-size: 14px;
}

.order-edit-modal__footer {
    position: sticky;
    bottom: 0;
    align-items: center;
    justify-content: flex-end;
    border-top: 1px solid #e5e7eb;
    background: rgba(255, 255, 255, .97);
}

.edit-button {
    min-height: 44px;
    padding: 0 20px;
    border: 0;
    border-radius: 11px;
    font-weight: 750;
    cursor: pointer;
}

.edit-button:disabled {
    opacity: .6;
    cursor: wait;
}

.edit-button--secondary {
    background: #eef2f6;
    color: #344054;
}

.edit-button--primary {
    background: #f58220;
    color: #fff;
    box-shadow: 0 8px 18px rgba(245, 130, 32, .25);
}

@media (max-width: 640px) {
    .order-edit-backdrop {
        align-items: flex-end;
        padding: 0;
    }

    .order-edit-modal {
        max-height: 94vh;
        border-radius: 20px 20px 0 0;
    }

    .order-edit-modal__header,
    .order-edit-modal__body,
    .order-edit-modal__footer {
        padding-right: 18px;
        padding-left: 18px;
    }

    .edit-option {
        align-items: flex-start;
        flex-direction: column;
    }
}
</style>
