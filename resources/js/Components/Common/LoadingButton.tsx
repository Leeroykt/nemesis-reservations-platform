import React from 'react';

interface LoadingButtonProps {
  isLoading: boolean;
  onClick?: () => void;
  type?: 'button' | 'submit' | 'reset';
  className?: string;
  disabled?: boolean;
  children: React.ReactNode;
}

export default function LoadingButton({
  isLoading,
  onClick,
  type = 'button',
  className = 'btn btn-gold',
  disabled = false,
  children,
}: LoadingButtonProps) {
  return (
    <button
      type={type}
      className={className}
      onClick={onClick}
      disabled={disabled || isLoading}
    >
      {isLoading ? (
        <>
          <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
          Loading...
        </>
      ) : (
        children
      )}
    </button>
  );
}