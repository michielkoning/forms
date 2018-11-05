<?php

function translate_form_errors() {
  $translations = [
    'form'              => [
      'submitError'     => __('Something went wrong when sending the form. Try again.', 'mk_forms'),
      'missingFields'   => __('Not all fields are filled in correctly.', 'mk_forms'),
    ],
    'field'             => [
      'typeMismatch'    => [
        'email'         => __('Please enter an email address.', 'mk_forms'),
        'url'           => __('Please enter a valid URL.', 'mk_forms'),
      ],
      'tooShort'        => __('Please lengthen this text to %minLength% characters or more. You are currently using %length% characters.', 'mk_forms'),
      'tooLong'         => __('Please shorten this text to no more than %maxLength% characters. You are currently using %length% characters.', 'mk_forms'),
      'badInput'        => __('Please enter a number.', 'mk_forms'),
      'stepMismatch'    => __('Please select a valid value.', 'mk_forms'),
      'rangeOverflow'   => __('Please select a value that is no more than %max%.', 'mk_forms'),
      'rangeUnderflow'  => __('Please select a value that is no less than %min%.', 'mk_forms'),
      'patternMismatch' => __('Please match the requested format.', 'mk_forms'),
      'valueMissing'    => __('Please enter your %label%.', 'mk_forms'),
      'invalid'         => __('The value you entered for this field is invalid.', 'mk_forms'),
    ]
  ];
  return $translations;
}
